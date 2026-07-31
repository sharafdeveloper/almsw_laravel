<?php

namespace App\Services;

class AutoBackupService
{

    public function createBackup()
    {
        

        
        
        $fileName = $this->generateFileName();

        $backupPath = $this->getBackupPath();
      
       


    if ($this->backupAlreadyExists($backupPath, $fileName)) {
        
        return;

    }
    
    

    $fullPath = $backupPath . '/' . $fileName;

    // dd("BEFORE createDatabaseBackup");
    $this->createDatabaseBackup($fullPath);

    $this->validateBackup($fullPath);

    }


    private function generateFileName()
    {
        return 'POS_DB_' . now()->format('Y_m_d_Hi') . '.sql';

    }


    private function getBackupPath()
    {
        return config('app.backup_path');

    }


    private function backupAlreadyExists($backupPath, $fileName)
    {
        return file_exists($backupPath . DIRECTORY_SEPARATOR . $fileName);

    }


    private function createDatabaseBackup($fullPath)
    {
        
        $host = config('database.connections.mysql.host');

        $database = config('database.connections.mysql.database');

        $username = config('database.connections.mysql.username');

        $password = config('database.connections.mysql.password');

        $mysqldumpPath = env('MYSQLDUMP_PATH');

         $command = '"' . $mysqldumpPath . '"';

    $command .= ' --host=' . $host;

    $command .= ' --user=' . $username;


    if (!empty($password)) {

        $command .= ' --password=' . $password;

    }


    $command .= ' ' . $database;

    $command .= ' > "' . $fullPath . '"';


    $output = [];

$result = 0;

exec(

    $command . ' 2>&1',

    $output,

    $result

);
if ($result !== 0) {
    throw new \Exception(implode("\n", $output));
}




    }


    private function validateBackup($fullPath)
    {
        if (!file_exists($fullPath)) {

        throw new \Exception('Backup file was not created.');

    }
    }

}