<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class RestoreService
{
    public function restoreDatabase($path)
    {
        // Full path of the uploaded SQL file
        $backupFile = storage_path('app/private/' . $path);


        

        if (!File::exists($backupFile)) {
            throw new \Exception('Backup file not found.');
        }

        // Database configuration
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port');

        // XAMPP mysql.exe location
        $mysql = 'C:\\xampp\\mysql\\bin\\mysql.exe';

        // Build restore command
        $command = "\"{$mysql}\" "
            . "--host={$host} "
            . "--port={$port} "
            . "--user={$username} ";

        if (!empty($password)) {
            $command .= "--password={$password} ";
        }

        $command .= "{$database} < \"{$backupFile}\"";

        exec($command, $output, $result);

        if ($result !== 0) {
            throw new \Exception('Database restore failed.');
        }

        return true;
    }
}