<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use App\Models\Backup;

class BackupService
{
    public function databaseBackup()
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME');
        $password = env('DB_PASSWORD');
        $host     = env('DB_HOST');
        $port     = env('DB_PORT');

        $filename = 'database_' . date('Y-m-d_H-i-s') . '.sql';

        $backupPath = storage_path('app/backups/database');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $file = $backupPath . DIRECTORY_SEPARATOR . $filename;

        // XAMPP mysqldump location
        $mysqldump = '/usr/bin/mysqldump';

        $command = "\"{$mysqldump}\" "
            . "--host={$host} "
            . "--port={$port} "
            . "--user={$username} ";

        if (!empty($password)) {
            $command .= "--password={$password} ";
        }

        $command .= "{$database} > \"{$file}\"";

        exec($command, $output, $result);

        // if ($result !== 0 || !File::exists($file)) {
        //     throw new \Exception('Database backup failed.');
        // }
        if ($result !== 0 || !File::exists($file)) {
         throw new \Exception(
        "Exit Code: {$result}\n\nCommand:\n{$command}\n\nOutput:\n" . implode("\n", $output)
        );
    }
        
        Backup::create([
            'file_name' => basename($file),
            'original_name' => basename($file),
            'type' => 'database',
            'file_path' => $file,
            'file_size' => filesize($file),
            'mime_type' => 'application/sql',
            'created_by' => auth()->check()
          ? auth()->user()->name: 'System',
]);
        return $file;
    }








}