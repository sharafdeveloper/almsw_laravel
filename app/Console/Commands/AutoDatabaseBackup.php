<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutoBackupService;


class AutoDatabaseBackup extends Command
{

    protected $signature = 'backup:auto';

    protected $description = 'Automatically creates database backups';


    public function handle(AutoBackupService $backupService)
    {
        //   $backupService->createBackup();
        //  dd("COMMAND WORKING");
         $backupService->createBackup();
    

    }

}

