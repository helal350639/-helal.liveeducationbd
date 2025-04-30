<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
  public function downloadDatabase()
  {
    $dbName = env('DB_DATABASE');
    $dbUser = env('DB_USERNAME');
    $dbPass = env('DB_PASSWORD');
    $dbHost = env('DB_HOST');
    
    $fileName = $dbName . '_backup_' . date('Y_m_d_His') . '.sql';
    $filePath = storage_path('app/' . $fileName);

    // Create a database dump
    $command = "mysqldump --user={$dbUser} --password=\"{$dbPass}\" --host={$dbHost} {$dbName} > {$filePath}";
    $result = null;
    $output = null;
    exec($command, $output, $result);

    if ($result !== 0) {
      return response()->json(['error' => 'Database backup failed.'], 500);
    }

    // Return the file as download
    return response()->download($filePath)->deleteFileAfterSend(true);
  }
}
