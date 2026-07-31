<?php

namespace App\Services;

class BackupValidatorService
{
    public function validate($path)
{
    $fullPath = storage_path('app/private/' . $path);
    $fileSize = filesize($fullPath);
    $size = round($fileSize / 1024 / 1024, 2) . ' MB';

    if (!file_exists($fullPath)) {
        return [
            'status' => false,
            'message' => 'Backup file not found.'
        ];
    }

    $handle = fopen($fullPath, 'r');

    $database = null;
    $server = null;
    $tableCount = 0;
    $insertCount = 0;
    $lines = 0;

    while (($line = fgets($handle)) !== false) {

        // Extract database name
        if (str_contains($line, 'Database:')) {

            preg_match(
                '/Database:\s*(.*)/',
                $line,
                $matches
            );

            if (isset($matches[1])) {
                $database = trim($matches[1]);
            }
        }


        // Extract server version
        if (str_contains($line, 'Server version')) {

            preg_match(
                '/Server version\s+(.*)/',
                $line,
                $matches
            );

            if (isset($matches[1])) {
                $server = trim($matches[1]);
            }
        }
        // Count tables
    if (str_contains(strtoupper($line), 'CREATE TABLE')) {
        $tableCount++;
    }
    // Count insert statements
    if (str_contains(strtoupper($line), 'INSERT INTO')) {
        $insertCount++;
}


        $lines++;
    }

    fclose($handle);


    return [
    'status' => true,
    'database' => $database,
    'server' => $server,
    'size' => $size,
    'tables' => $tableCount,
    'records' => $insertCount,
    'lines_read' => $lines
];
}
}