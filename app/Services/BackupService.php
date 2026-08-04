<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use App\Models\Backup;

class BackupService
{
    public function databaseBackup()
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port');

        $filename = 'database_' . date('Y-m-d_H-i-s') . '.sql';

        $backupPath = storage_path('app/backups/database');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $file = $backupPath . DIRECTORY_SEPARATOR . $filename;

        // XAMPP mysqldump location
        $mysqldump = '/usr/bin/mysqldump';

        $command = escapeshellcmd($mysqldump)
    . ' --host=' . escapeshellarg($host)
    . ' --port=' . escapeshellarg($port)
    . ' --user=' . escapeshellarg($username);

if (!empty($password)) {
    $command .= ' --password=' . escapeshellarg($password);
}

$command .= ' ' . escapeshellarg($database);
$command .= ' > ' . escapeshellarg($file);


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