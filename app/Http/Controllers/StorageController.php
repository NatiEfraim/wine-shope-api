<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;


class StorageController extends Controller
{
    //
        /**
     * Upload the Laravel log file to the S3 storage
     * @return int
     */

    public function logToS3(): int
    {
        try {

            $localPath = storage_path('logs/laravel.log');

            if (!file_exists($localPath)) {
                Log::error('Error in LogService: logToS3 function: File does not exist in storage');
                return Response::HTTP_OK;
            }
            
            $diskName = config('filesystems.storage_service');
            $bucket = config("filesystems.disks..bucket");
      
            $user = Auth::user();
            $personalId = $user?->personal_id;
            $fileName = 'date_time_' . now()->format('Y-m-d_H-i-s');

            if ($personalId) {
                $fileName .= '_' . $personalId;
            }

            $fileName .= '.log';

            $s3Path =  $bucket . config('filesystems.files_name.log_folder_name') . '/' . $fileName;

            $res = Storage::disk($diskName)->put($s3Path, file_get_contents($localPath));
            if (!$res) {
                return Response::HTTP_INTERNAL_SERVER_ERROR;
            }
            return Response::HTTP_OK;
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error('Error in StorageService: handle function:' . $e->getMessage());
            return Response::HTTP_INTERNAL_SERVER_ERROR;
        }
    }
}
