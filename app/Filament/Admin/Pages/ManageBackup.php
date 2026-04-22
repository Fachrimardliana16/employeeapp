<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Output\BufferedOutput;

class ManageBackup extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static string $view = 'filament.admin.pages.manage-backup';

    protected static ?string $navigationGroup = 'Sistem';

    protected static ?string $navigationLabel = 'Kelola Backup';

    protected static ?string $title = 'Kelola Backup';

    protected static ?string $slug = 'manage-backup';

    protected static ?int $navigationSort = 11;

    public string $consoleOutput = '';

    public function getBackups(): Collection
    {
        $backupDisk = Storage::disk('local');
        $backupFolder = 'employeeapp';

        if (!$backupDisk->exists($backupFolder)) {
            return collect();
        }

        $files = $backupDisk->files($backupFolder);

        return collect($files)->map(function ($file) use ($backupDisk) {
            $fullPath = $backupDisk->path($file);
            $type = 'Database';

            $filename = basename($file);
            if (str_contains($filename, '-per-table')) {
                $type = 'Per Table';
            } elseif (file_exists($fullPath)) {
                $zip = new \ZipArchive();
                if ($zip->open($fullPath) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $stat = $zip->statIndex($i);
                        $fileName = $stat['name'];
                        if (!str_starts_with($fileName, 'db-dumps/')) {
                            $type = 'Lengkap';
                            break;
                        }
                    }
                    $zip->close();
                }
            }

            return [
                'name' => $filename,
                'size' => round($backupDisk->size($file) / 1024 / 1024, 2) . ' MB',
                'type' => $type,
                'created_at' => Carbon::createFromTimestamp($backupDisk->lastModified($file))->format('d/m/Y H:i:s'),
                'path' => $file,
            ];
        })->sortByDesc('created_at');
    }

    // =========================================================================
    // BACKUP ACTIONS
    // =========================================================================

    public function runTableLevelBackup()
    {
        $this->consoleOutput = "=== Table-Level Backup (Hosting-Compatible) ===\n";

        try {
            set_time_limit(900);

            $backupFolder = 'employeeapp';
            $filename = Carbon::now()->format('Y-m-d-H-i-s') . '-per-table.zip';
            $tempDir = storage_path('app/backup-temp');
            $tempPath = $tempDir . '/' . $filename;

            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception("Cannot create zip file at $tempPath");
            }

            $tables = $this->getTableNames();
            $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
            $this->consoleOutput .= "Driver: $driver | Tables found: " . count($tables) . "\n\n";

            foreach ($tables as $table) {
                $this->consoleOutput .= "Backing up: $table... ";

                $tempSqlFile = $tempDir . '/tbl-' . md5($table) . '.sql';
                $handle = fopen($tempSqlFile, 'w');
                fwrite($handle, "-- Table: $table\n\n");

                $wrappedTable = $this->wrapTableName($table);

                // Schema
                try {
                    if ($driver === 'sqlite') {
                        $schema = \Illuminate\Support\Facades\DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table]);
                        if (!empty($schema)) {
                            fwrite($handle, $schema[0]->sql . ";\n\n");
                        }
                    } else {
                        $schemaRows = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE $wrappedTable");
                        if (!empty($schemaRows)) {
                            $schemaRow = (array) $schemaRows[0];
                            $createSql = $schemaRow['Create Table'] ?? $schemaRow['Create View'] ?? null;
                            if ($createSql) {
                                fwrite($handle, $createSql . ";\n\n");
                            }
                        }
                    }
                } catch (\Exception $e) {
                    fwrite($handle, "-- Schema error: " . $e->getMessage() . "\n\n");
                    $this->consoleOutput .= "[schema skip] ";
                }

                // Data
                try {
                    $rowCount = 0;
                    $orderCol = $this->getFirstColumn($table);
                    \Illuminate\Support\Facades\DB::table($table)->orderBy($orderCol)->chunk(500, function ($rows) use ($handle, $wrappedTable, &$rowCount) {
                        foreach ($rows as $row) {
                            $rowArray = (array) $row;
                            $keys = array_keys($rowArray);
                            $values = array_values($rowArray);

                            $escapedValues = array_map(function ($v) {
                                if ($v === null) return 'NULL';
                                if (is_numeric($v)) return $v;
                                return "'" . addslashes((string) $v) . "'";
                            }, $values);

                            fwrite($handle, "INSERT INTO $wrappedTable (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n");
                            $rowCount++;
                        }
                    });
                    $this->consoleOutput .= "$rowCount rows OK\n";
                } catch (\Exception $e) {
                    fwrite($handle, "-- Data error: " . $e->getMessage() . "\n");
                    $this->consoleOutput .= "DATA ERROR: " . $e->getMessage() . "\n";
                }

                fclose($handle);
                $zip->addFile($tempSqlFile, "$table.sql");

                usleep(100000); // 0.1s pause
            }

            $zip->close();

            // Cleanup temp SQL files
            foreach ($tables as $table) {
                $f = $tempDir . '/tbl-' . md5($table) . '.sql';
                if (file_exists($f)) @unlink($f);
            }

            Storage::disk('local')->putFileAs($backupFolder, new \Illuminate\Http\File($tempPath), $filename);
            @unlink($tempPath);

            $this->consoleOutput .= "\n--- Table-Level Backup Completed Successfully ---";
            $this->consoleOutput .= "\nFile: $filename";
            Notification::make()->title('Backup Per Tabel Selesai!')->success()->send();

        } catch (\Exception $e) {
            $this->consoleOutput .= "\nFATAL ERROR: " . $e->getMessage();
            Notification::make()->title('Backup Per Tabel Gagal')->danger()->send();
        }
    }

    public function runDbBackup()
    {
        $this->consoleOutput = "=== Database Backup (Pure PHP) ===\n";

        try {
            set_time_limit(300);

            $backupFolder = 'employeeapp';
            $filename = Carbon::now()->format('Y-m-d-H-i-s') . '.zip';
            $tempDir = storage_path('app/backup-temp');
            $tempPath = $tempDir . '/' . $filename;

            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception("Cannot create zip file at $tempPath");
            }

            $this->generatePurePhpDbDump($zip, 'db-dumps/database-backup.sql');

            $zip->close();

            // Cleanup temp db dump
            $tempDbFile = $tempDir . '/full-db-dump.sql';
            if (file_exists($tempDbFile)) @unlink($tempDbFile);

            Storage::disk('local')->putFileAs($backupFolder, new \Illuminate\Http\File($tempPath), $filename);
            @unlink($tempPath);

            $this->consoleOutput .= "\n--- Database Backup Completed Successfully ---";
            Notification::make()->title('Backup Database Selesai!')->success()->send();

        } catch (\Exception $e) {
            $this->consoleOutput .= "\nFATAL ERROR: " . $e->getMessage();
            Notification::make()->title('Backup Database Gagal')->danger()->send();
        }
    }

    public function runFullBackup()
    {
        $this->consoleOutput = "=== Full Backup (Pure PHP) ===\n";

        try {
            set_time_limit(900);

            $backupFolder = 'employeeapp';
            $filename = Carbon::now()->format('Y-m-d-H-i-s') . '.zip';
            $tempDir = storage_path('app/backup-temp');
            $tempPath = $tempDir . '/' . $filename;

            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception("Cannot create zip file at $tempPath");
            }

            // 1. DB
            $this->consoleOutput .= "Step 1: Dumping Database...\n";
            $this->generatePurePhpDbDump($zip, 'db-dumps/database-backup.sql');

            // 2. Files
            $this->consoleOutput .= "Step 2: Archiving Files...\n";
            $rootPath = base_path();
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($rootPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            $excludes = ['vendor', 'node_modules', '.git', 'backup-temp'];
            $count = 0;

            foreach ($iterator as $file) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                $skip = false;
                foreach ($excludes as $ex) {
                    if (str_contains($relativePath, $ex)) {
                        $skip = true;
                        break;
                    }
                }

                if (!$skip) {
                    $zip->addFile($filePath, $relativePath);
                    $count++;
                    if ($count % 500 === 0) {
                        $this->consoleOutput .= "Added $count files...\n";
                    }
                }
            }

            $zip->close();

            // Cleanup temp db dump
            $tempDbFile = $tempDir . '/full-db-dump.sql';
            if (file_exists($tempDbFile)) @unlink($tempDbFile);

            Storage::disk('local')->putFileAs($backupFolder, new \Illuminate\Http\File($tempPath), $filename);
            @unlink($tempPath);

            $this->consoleOutput .= "\n--- Full Backup Completed Successfully ---";
            $this->consoleOutput .= "\n$count files archived.";
            Notification::make()->title('Backup Lengkap Selesai!')->success()->send();

        } catch (\Exception $e) {
            $this->consoleOutput .= "\nFATAL ERROR: " . $e->getMessage();
            Notification::make()->title('Backup Lengkap Gagal')->danger()->send();
        }
    }

    // =========================================================================
    // DOWNLOAD / DELETE
    // =========================================================================

    public function downloadBackup($path)
    {
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path);
        }
        Notification::make()->title('Berkas tidak ditemukan')->danger()->send();
    }

    public function deleteBackup($path)
    {
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
            Notification::make()->title('Backup berhasil dihapus')->success()->send();
        }
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Generate a full SQL dump and add it to the zip archive.
     * Streams data to a temp file to keep memory usage minimal.
     */
    protected function generatePurePhpDbDump(\ZipArchive $zip, string $innerPath): void
    {
        $tables = $this->getTableNames();
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

        $tempDir = storage_path('app/backup-temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempSqlFile = $tempDir . '/full-db-dump.sql';
        $handle = fopen($tempSqlFile, 'w');
        fwrite($handle, "-- HRIS Database Dump\n-- Generated: " . now()->toDateTimeString() . "\n-- Driver: $driver\n\n");

        $this->consoleOutput .= "Found " . count($tables) . " tables to dump.\n";

        foreach ($tables as $table) {
            $wrappedTable = $this->wrapTableName($table);
            fwrite($handle, "-- ========================================\n");
            fwrite($handle, "-- Table: $table\n");
            fwrite($handle, "-- ========================================\n\n");

            // Schema
            try {
                if ($driver === 'sqlite') {
                    $schema = \Illuminate\Support\Facades\DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table]);
                    if (!empty($schema)) {
                        fwrite($handle, $schema[0]->sql . ";\n\n");
                    }
                } else {
                    $schemaRows = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE $wrappedTable");
                    if (!empty($schemaRows)) {
                        $schemaRow = (array) $schemaRows[0];
                        $createSql = $schemaRow['Create Table'] ?? $schemaRow['Create View'] ?? null;
                        if ($createSql) {
                            fwrite($handle, $createSql . ";\n\n");
                        }
                    }
                }
            } catch (\Exception $e) {
                fwrite($handle, "-- Schema error: " . $e->getMessage() . "\n\n");
            }

            // Data
            try {
                $orderCol = $this->getFirstColumn($table);
                \Illuminate\Support\Facades\DB::table($table)->orderBy($orderCol)->chunk(500, function ($rows) use ($handle, $wrappedTable) {
                    foreach ($rows as $row) {
                        $rowArray = (array) $row;
                        $keys = array_keys($rowArray);
                        $values = array_values($rowArray);

                        $escapedValues = array_map(function ($v) {
                            if ($v === null) return 'NULL';
                            if (is_numeric($v)) return $v;
                            return "'" . addslashes((string) $v) . "'";
                        }, $values);

                        fwrite($handle, "INSERT INTO $wrappedTable (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n");
                    }
                });
            } catch (\Exception $e) {
                fwrite($handle, "-- Data error: " . $e->getMessage() . "\n");
            }

            fwrite($handle, "\n");
            usleep(50000); // 50ms pause between tables
        }

        fclose($handle);
        $zip->addFile($tempSqlFile, $innerPath);
    }

    /**
     * Get table names for the CURRENT database only.
     *
     * CRITICAL: Laravel's getTableListing() on MySQL returns cross-database
     * references (e.g. "other_db.some_table") which causes errors.
     * We use SHOW TABLES instead, which only returns current DB tables.
     */
    protected function getTableNames(): array
    {
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return \Illuminate\Support\Facades\DB::connection()->getSchemaBuilder()->getTableListing();
        }

        // MySQL: SHOW TABLES only returns tables from the current database
        $results = \Illuminate\Support\Facades\DB::select('SHOW TABLES');

        $tables = [];
        foreach ($results as $row) {
            $rowArray = (array) $row;
            // SHOW TABLES returns a column named "Tables_in_{database_name}"
            $tables[] = reset($rowArray);
        }

        return $tables;
    }

    /**
     * Wrap a table name in backticks for raw SQL.
     * Handles dot-notation (db.table) by wrapping each part separately.
     */
    protected function wrapTableName(string $table): string
    {
        if (str_contains($table, '.')) {
            $parts = explode('.', $table);
            return '`' . implode('`.`', $parts) . '`';
        }
        return '`' . $table . '`';
    }

    /**
     * Get the first column name of a table for use in orderBy.
     * Required because Laravel's chunk() demands an orderBy clause.
     */
    protected function getFirstColumn(string $table): string
    {
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

        try {
            if ($driver === 'sqlite') {
                $columns = \Illuminate\Support\Facades\DB::select("PRAGMA table_info('$table')");
                if (!empty($columns)) {
                    return $columns[0]->name;
                }
            } else {
                $wrappedTable = $this->wrapTableName($table);
                $columns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM $wrappedTable");
                if (!empty($columns)) {
                    return $columns[0]->Field;
                }
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return 'id'; // Fallback to 'id' which most tables have
    }
}
