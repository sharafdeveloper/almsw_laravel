<?php
 
namespace App\Http\Controllers;
 
use App\Services\BackupService;
use Illuminate\Http\Request;
use App\Models\Backup;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Services\BackupValidatorService;
use App\Services\RestoreService;
use Carbon\Carbon;




class BackupController extends Controller
{
    protected $backupService;
    protected $backupValidatorService;
    protected $restoreService;
    


   public function __construct(
    BackupService $backupService,
    BackupValidatorService $backupValidatorService,
    RestoreService $restoreService
)
{
    $this->backupService = $backupService;
    $this->backupValidatorService = $backupValidatorService;
    $this->restoreService = $restoreService;
}


    public function index()
    {
        

     $backups = Backup::latest()->get();

    $morningBackupStatus = 'Pending';
    $morningBackupTime = null;

    $nightBackupStatus = 'Pending';
    $nightBackupTime = null;

    return view('backup.index', compact(
        'backups',
        'morningBackupStatus',
        'morningBackupTime',
        'nightBackupStatus',
        'nightBackupTime'
    ));
    }


    public function download($id)
    {
        $backup = Backup::findOrFail($id);

        return response()->download($backup->file_path);
    }


    public function validateBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:512000',
        ]);


        $file = $request->file('backup_file');

        $fileName = time() . '_' . $file->getClientOriginalName();


        $path = $file->storeAs(
            'backups/restore-temp',
            $fileName,
            'local'
        );


        $result = $this->backupValidatorService->validate($path);

        $result['backup_path'] = $path;
        return view(
    'backup.restore-information',
    compact('result')
    
);
    }



    public function restoreReady(Request $request)
{

    $result = $request->all();


    return view(
        'backup.restore-execute',
        compact('result')
    );

}
public function restoreExecute(Request $request)
{
    $backupPath = $request->backup_path;

    // Step 1: Create a backup of the current database
    $currentBackup = $this->backupService->databaseBackup();

    // Step 2: Restore the uploaded SQL backup
    $this->restoreService->restoreDatabase($backupPath);

    // Step 3: Show success page
    return view(
        'backup.restore-success',
        compact('currentBackup')
    );
}
    



    public function restore(Request $request)
    {
        dd($this->restoreService);

    }
    



    public function database()
    {
        $file = $this->backupService->databaseBackup();

        return response()->download($file)->deleteFileAfterSend(false);

       }
    



    public function restoreConfirmation(Request $request)
    {
        $result = $request->all();


        return view(
            'backup.restore-confirmation',
            compact('result')
        );
    }



    public function fullBackup()
    {
        Artisan::call('backup:run');

        $disk = Storage::disk('local');

        $files = $disk->allFiles();


        $backupFiles = collect($files)
            ->filter(function ($file) {
                return str_contains($file, '.zip');
            })
            ->sortDesc();


        if ($backupFiles->isEmpty()) {
            return back()->with('error', 'Backup file not found.');
        }


        $latestBackup = $backupFiles->first();

        $path = $disk->path($latestBackup);


        Backup::create([
            'file_name' => basename($path),
            'original_name' => basename($path),
            'type' => 'application',
            'file_path' => $path,
            'file_size' => filesize($path),
            'mime_type' => 'application/zip',
            'created_by' => auth()->user()->name,
        ]);


        return $disk->download($latestBackup);
    }

}