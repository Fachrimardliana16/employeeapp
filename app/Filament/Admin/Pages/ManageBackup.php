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
        $this->consoleOutput = "Starting Table-Level Backup process...\n";
        
        try {
            set_time_limit(600); // 10 minutes
            
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
            
            foreach ($tables as $table) {
                $this->consoleOutput .= "Backing up table: $table...\n";
                
                $sql = "-- Table: $table\n";
                
                // Get Schema (Specific for SQLite)
                $schema = \Illuminate\Support\Facades\DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=:table", ['table' => $table]);
                if (!empty($schema)) {
                    $sql .= $schema[0]->sql . ";\n\n";
                }

                // Get Data
                $rows = \Illuminate\Support\Facades\DB::table($table)->get();
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    $keys = array_keys($rowArray);
                    $values = array_values($rowArray);
                    
                    $escapedValues = array_map(function($value) {
                        if ($value === null) return 'NULL';
                        if (is_numeric($value)) return $value;
                        return "'" . str_replace("'", "''", $value) . "'";
                    }, $values);

                    $sql .= "INSERT INTO \"$table\" (\"" . implode('", "', $keys) . "\") VALUES (" . implode(', ', $escapedValues) . ");\n";
                }

                $zip->addFromString("$table.sql", $sql);
            }

            $zip->close();

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
        $this->runBackupCommand();
    }

    public function runDbBackup()
    {
        $this->runBackupCommand(['--only-db' => true]);
    }

    protected function runBackupCommand(array $params = [])
    {
        $this->consoleOutput = "Starting backup process...\n";
        
        try {
            // Set large timeout for long running backup
            set_time_limit(300);
            
            $output = new BufferedOutput();
            
            Artisan::call('backup:run', array_merge($params, ['--no-interaction' => true]), $output);
            
            $this->consoleOutput .= $output->fetch();
            $this->consoleOutput .= "\n--- Backup Completed Successfully ---";

            Notification::make()->title('Backup Selesai!')->success()->send();
        } catch (\Exception $e) {
            $this->consoleOutput .= "\nERROR: " . $e->getMessage();
            Notification::make()->title('Backup Gagal')->danger()->send();
        }
    }
}
