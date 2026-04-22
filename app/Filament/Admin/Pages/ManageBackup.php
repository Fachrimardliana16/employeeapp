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
            
            // Check filename for explicit type first
            $filename = basename($file);
            if (str_contains($filename, '-per-table')) {
                $type = 'Per Table';
            } else if (file_exists($fullPath)) {
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

    public function runTableLevelBackup()
    {
        $this->consoleOutput = "Starting Table-Level Backup (Hosting-Compatible Mode)...\n";
        
        try {
            set_time_limit(900); // 15 minutes
            
            $backupFolder = 'employeeapp';
            $filename = \Illuminate\Support\Carbon::now()->format('Y-m-d-H-i-s') . '-per-table.zip';
            $tempPath = storage_path('app/backup-temp/' . $filename);
            
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception("Cannot create zip file at $tempPath");
            }

            $tables = \Illuminate\Support\Facades\DB::connection()->getSchemaBuilder()->getTableListing();
            $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
            
            foreach ($tables as $table) {
                $this->consoleOutput .= "Backing up table: $table...\n";
                
                $tempSqlFile = storage_path('app/backup-temp/tbl-' . md5($table) . '.sql');
                $handle = fopen($tempSqlFile, 'w');
                fwrite($handle, "-- Table: $table\n-- Generated via Pure PHP Gentle Mode\n\n");
                
                // Get Schema (Driver Aware)
                $wrappedTable = \Illuminate\Support\Facades\DB::connection()->getQueryGrammar()->wrapTable($table);
                if ($driver === 'sqlite') {
                    $schema = \Illuminate\Support\Facades\DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=:table", ['table' => $table]);
                    if (!empty($schema)) {
                        fwrite($handle, $schema[0]->sql . ";\n\n");
                    }
                } else {
                    // MySQL
                    $schema = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE $wrappedTable")[0];
                    $schemaKey = 'Create Table';
                    fwrite($handle, $schema->$schemaKey . ";\n\n");
                }

                // Get Data using Chunks (Memory Efficient Streaming)
                \Illuminate\Support\Facades\DB::table($table)->orderBy(\Illuminate\Support\Facades\DB::raw(1))->chunk(500, function($rows) use ($handle, $wrappedTable) {
                    foreach ($rows as $row) {
                        $rowArray = (array)$row;
                        $keys = array_keys($rowArray);
                        $values = array_values($rowArray);
                        
                        $escapedValues = array_map(function($value) {
                            if ($value === null) return 'NULL';
                            if (is_numeric($value)) return $value;
                            return "'" . str_replace("'", "''", $value) . "'";
                        }, $values);

                        fwrite($handle, "INSERT INTO $wrappedTable (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n");
                    }
                });

                fclose($handle);
                $zip->addFile($tempSqlFile, "$table.sql");
                
                // GENTLE MODE: Small pause to prevent hitting DB limits
                usleep(100000); // 0.1 second
            }

            $zip->close();
            
            // Clean up individual temp SQL files after zip is closed
            foreach ($tables as $table) {
                $tempSqlFile = storage_path('app/backup-temp/tbl-' . md5($table) . '.sql');
                if (file_exists($tempSqlFile)) @unlink($tempSqlFile);
            }

            // Move to final destination
            \Illuminate\Support\Facades\Storage::disk('local')->putFileAs($backupFolder, new \Illuminate\Http\File($tempPath), $filename);
            @unlink($tempPath);

            $this->consoleOutput .= "\n--- Table-Level Backup Completed Successfully ---";
            $this->consoleOutput .= "\nBackup file: $filename";

            \Filament\Notifications\Notification::make()->title('Backup Per Tabel Selesai!')->success()->send();
        } catch (\Exception $e) {
            $this->consoleOutput .= "\nERROR: " . $e->getMessage();
            \Filament\Notifications\Notification::make()->title('Backup Per Tabel Gagal')->danger()->send();
        }
    }

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

    public function runFullBackup()
    {
        $this->consoleOutput = "Starting Full Backup (Pure PHP Mode)...\n";
        $this->consoleOutput .= "This process may take a while depending on file count.\n";
        
        try {
            set_time_limit(900); // 15 mins
            $backupFolder = 'employeeapp';
            $filename = \Illuminate\Support\Carbon::now()->format('Y-m-d-H-i-s') . '.zip';
            $tempPath = storage_path('app/backup-temp/' . $filename);
            
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception("Cannot create zip file at $tempPath");
            }

            // 1. DUMP DATABASE
            $this->consoleOutput .= "Step 1: Dumping Database...\n";
            $this->generatePurePhpDbDump($zip, 'db-dumps/database-backup.sql');

            // 2. ARCHIVE FILES
            $this->consoleOutput .= "Step 2: Archiving Files...\n";
            $rootPath = base_path();
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($rootPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            $excludePaths = [
                DIRECTORY_SEPARATOR . 'vendor',
                DIRECTORY_SEPARATOR . 'node_modules',
                DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'backup-temp',
                DIRECTORY_SEPARATOR . '.git'
            ];

            $count = 0;
            foreach ($files as $name => $file) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Check exclusions
                $shouldExclude = false;
                foreach ($excludePaths as $exclude) {
                    if (str_contains(DIRECTORY_SEPARATOR . $relativePath, $exclude)) {
                        $shouldExclude = true;
                        break;
                    }
                }

                if (!$shouldExclude) {
                    $zip->addFile($filePath, $relativePath);
                    $count++;
                    if ($count % 500 === 0) {
                        $this->consoleOutput .= "Added $count files...\n";
                    }
                }
            }

            $zip->close();

            // 3. STORAGE
            \Illuminate\Support\Facades\Storage::disk('local')->putFileAs($backupFolder, new \Illuminate\Http\File($tempPath), $filename);
            @unlink($tempPath);

            $this->consoleOutput .= "\n--- Full Backup Completed Successfully ---";
            $this->consoleOutput .= "\nBackup file: $filename ($count files archived)";

            \Filament\Notifications\Notification::make()->title('Backup Lengkap Selesai!')->success()->send();
        } catch (\Exception $e) {
            $this->consoleOutput .= "\nERROR: " . $e->getMessage();
            \Filament\Notifications\Notification::make()->title('Backup Lengkap Gagal')->danger()->send();
        }
    }

    public function runDbBackup()
    {
        $this->consoleOutput = "Starting DB Backup (Pure PHP Mode)...\n";
        
        try {
            set_time_limit(300);
            $backupFolder = 'employeeapp';
            $filename = \Illuminate\Support\Carbon::now()->format('Y-m-d-H-i-s') . '.zip';
            $tempPath = storage_path('app/backup-temp/' . $filename);
            
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception("Cannot create zip file at $tempPath");
            }

            $this->generatePurePhpDbDump($zip, 'db-dumps/database-backup.sql');
            
            $zip->close();

            \Illuminate\Support\Facades\Storage::disk('local')->putFileAs($backupFolder, new \Illuminate\Http\File($tempPath), $filename);
            @unlink($tempPath);

            $this->consoleOutput .= "\n--- Database Backup Completed Successfully ---";
            \Filament\Notifications\Notification::make()->title('Backup Database Selesai!')->success()->send();
        } catch (\Exception $e) {
            $this->consoleOutput .= "\nERROR: " . $e->getMessage();
            \Filament\Notifications\Notification::make()->title('Backup Database Gagal')->danger()->send();
        }
    }

    protected function generatePurePhpDbDump(\ZipArchive $zip, string $innerPath)
    {
        $tables = \Illuminate\Support\Facades\DB::connection()->getSchemaBuilder()->getTableListing();
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        
        $tempSqlFile = storage_path('app/backup-temp/full-db-dump.sql');
        $handle = fopen($tempSqlFile, 'w');
        fwrite($handle, "-- HRIS Database Dump\n-- Generated via Pure PHP Mode\n-- Driver: $driver\n\n");

        foreach ($tables as $table) {
            // Get Schema
            $wrappedTable = \Illuminate\Support\Facades\DB::connection()->getQueryGrammar()->wrapTable($table);
            if ($driver === 'sqlite') {
                $schema = \Illuminate\Support\Facades\DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=:table", ['table' => $table]);
                if (!empty($schema)) {
                    fwrite($handle, $schema[0]->sql . ";\n\n");
                }
            } else {
                // MySQL
                $schema = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE $wrappedTable")[0];
                $schemaKey = 'Create Table';
                fwrite($handle, $schema->$schemaKey . ";\n\n");
            }

            // Get Data (Chunked for memory efficiency streaming)
            \Illuminate\Support\Facades\DB::table($table)->orderBy(\Illuminate\Support\Facades\DB::raw(1))->chunk(500, function($rows) use ($handle, $wrappedTable) {
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    $keys = array_keys($rowArray);
                    $values = array_values($rowArray);
                    
                    $escapedValues = array_map(function($value) {
                        if ($value === null) return 'NULL';
                        if (is_numeric($value)) return $value;
                        return "'" . str_replace("'", "''", $value) . "'";
                    }, $values);

                    fwrite($handle, "INSERT INTO $wrappedTable (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $escapedValues) . ");\n");
                }
            });
            fwrite($handle, "\n");
        }

        fclose($handle);
        $zip->addFile($tempSqlFile, $innerPath);
        // Note: Clean up should happen after ZipArchive is closed in calling method
        // but for safety we'll try to delete it in a finally block or rely on parent
    }
}
