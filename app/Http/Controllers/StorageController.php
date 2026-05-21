<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\Booking;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

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
            $bucket = config('filesystems.disks.minio.bucket');

            $user = Auth::user();
            $personalId = $user?->personal_id;
            $fileName = 'date_time_' . now()->format('Y-m-d_H-i-s');

            if ($personalId) {
                $fileName .= '_' . $personalId;
            }

            $fileName .= '.log';

            $s3Path = $bucket . '/' . config('filesystems.folder_name.log_folder') . '/' . $fileName;

            $res = Storage::disk($diskName)->put($s3Path, file_get_contents($localPath));
            if (!$res) {
                return Response::HTTP_INTERNAL_SERVER_ERROR;
            }
            return Response::HTTP_OK;
        } catch (\Exception $e) {
            Log::error('Error in StorageService: handle function:' . $e->getMessage());
            return Response::HTTP_INTERNAL_SERVER_ERROR;
        }
    }

    //-----------------Exports fucntions
    public function exportBookingDataIntoXlsx()
    {
        try {
            $bookings = Booking::with(['user', 'status', 'items.product'])
                ->where('is_deleted', false)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($bookings->isEmpty()) {
                Log::info('No bookings found to export');
                return response()->json(
                    [
                        'message' => 'Users not found',
                    ],
                    Response::HTTP_BAD_REQUEST,
                );
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Optional RTL (if Hebrew UI)
            $sheet->setRightToLeft(true);

            // Headers
            $headers = [
                'A1' => 'מספר הזמנה',
                'B1' => 'שם משתמש',
                'C1' => 'סטטוס',
                'D1' => 'כמות',
                'E1' => 'מחיר יחידה',
                'F1' => 'סה״כ הזמנה',
                'G1' => 'תאריך יצירה',
            ];

            foreach ($headers as $cell => $text) {
                $sheet->setCellValue($cell, $text);
            }

            // Header style
            $sheet->getStyle('A1:G1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '336D4B'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

            $row = 2;

            foreach ($bookings as $booking) {
                foreach ($booking->items as $item) {
                    $sheet->setCellValue("A{$row}", $booking->serial_number);
                    $sheet->setCellValue("B{$row}", $booking->user->name ?? '-');
                    $sheet->setCellValue("C{$row}", $booking->status->name ?? '-');

                    $sheet->setCellValue("D{$row}", $item->quantity);
                    $sheet->setCellValue("E{$row}", $item->unit_price);
                    $sheet->setCellValue("F{$row}", $item->total_price);
                    $sheet->setCellValue("G{$row}", optional($booking->created_at)->format('d/m/Y H:i'));
                    $row++;
                }
            }

            // Center all cells
            $sheet->getStyle('A1:G' . ($row - 1))->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Auto size columns
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // File name
            $fileName = 'bookings.xlsx';
            $localPath = storage_path("app/{$fileName}");

            $writer = new Xlsx($spreadsheet);
            $writer->save($localPath);

            // Upload to MinIO / S3
            $this->uploadFileToBucket(localPath: $localPath, remoteDirectory: config('filesystems.folder_name.export_booking'));
            return response()->json(
                [
                    'message' => 'bookings exported successfully',
                ],
                Response::HTTP_OK,
            );
        } catch (\Exception $e) {
            Log::error('Error exporting bookings: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Failed to exported Bookings',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function exportUsersIntoXlsx()
    {
        try {
            $users = User::with('roles')->where('is_deleted', false)->orderBy('id')->get();
            if ($users->isEmpty()) {
                Log::info('No users found to export');
                return response()->json(
                    [
                        'message' => 'Users not found',
                    ],
                    Response::HTTP_BAD_REQUEST,
                );
            }
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setRightToLeft(true);

            $headers = [
                'A1' => 'שם',
                'B1' => 'אימייל',
                'C1' => 'תעודת זהות',
                'D1' => 'טלפון',
                'E1' => 'תפקיד',
            ];

            foreach ($headers as $cell => $text) {
                $sheet->setCellValue($cell, $text);
            }

            $sheet->getStyle('A1:E1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '336D4B'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

            $row = 2;

            foreach ($users as $user) {
                $role = $user->roles->first();

                $sheet->setCellValue("A{$row}", $user->name);
                $sheet->setCellValue("B{$row}", $user->email);
                $sheet->setCellValue("C{$row}", $user->personal_id);
                $sheet->setCellValue("D{$row}", $user->phone);
                $sheet->setCellValue("E{$row}", $role->id);

                $row++;
            }

            $sheet->getStyle('A1:E' . ($row - 1))->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            foreach (range('A', 'E') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $fileName = 'users.xlsx';
            $localPath = storage_path("app/{$fileName}");

            $writer = new Xlsx($spreadsheet);
            $writer->save($localPath);

            $this->uploadFileToBucket(localPath: $localPath, remoteDirectory: config('filesystems.folder_name.export_user'));

            return response()->json(
                [
                    'message' => 'Users exported successfully',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $e) {
            Log::error('Export users error: ' . $e->getMessage());
            return response()->json(
                [
                    'message' => 'Failed to exported users',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function exportProductsIntoXlsx()
    {
        try {
            $products = Product::where('is_deleted', false)->orderBy('id')->get();

            if ($products->isEmpty()) {
                Log::info('No products found to export');
                return response()->json(
                    [
                        'message' => 'Products not found to export',
                    ],
                    Response::HTTP_BAD_REQUEST,
                );
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setRightToLeft(true);

            $headers = [
                'A1' => 'מספר מקט',
                'B1' => 'שם מוצר',
                'C1' => 'תיאור',
                'D1' => 'מחיר',
                'E1' => 'כמות',
                'F1' => 'פעיל',
            ];

            foreach ($headers as $cell => $text) {
                $sheet->setCellValue($cell, $text);
            }

            $sheet->getStyle('A1:F1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],

                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => [
                        'rgb' => '336D4B',
                    ],
                ],

                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],

                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

            $row = 2;

            foreach ($products as $product) {
                $sheet->setCellValue("A{$row}", $product->sku);

                $sheet->setCellValue("B{$row}", $product->name);

                $sheet->setCellValue("C{$row}", $product->description ?? 'לא קיים');

                $sheet->setCellValue("D{$row}", $product->price);

                $sheet->setCellValue("E{$row}", $product->quantity);

                $sheet->setCellValue("F{$row}", $product->is_active ? 'כן' : 'לא');

                $row++;
            }

            $sheet->getStyle('A1:F' . ($row - 1))->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            foreach (range('A', 'F') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            $fileName = 'products.xlsx';
            $localPath = storage_path("app/{$fileName}");

            $writer = new Xlsx($spreadsheet);
            $writer->save($localPath);

            $this->uploadFileToBucket(localPath: $localPath, remoteDirectory: config('filesystems.folder_name.export_product'));

            return response()->json(
                [
                    'message' => 'Products exported successfully',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $e) {
            Log::error('Export products error: ' . $e->getMessage());

            return response()->json(
                [
                    'message' => 'Failed to loaded product',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    //----------------------imports functions
    public function importProductsFromXlsxBucket()
    {
        try {
            $diskName = config('filesystems.storage_service');
            $bucket = config("filesystems.disks.{$diskName}.bucket");

            $fileName = 'products.xlsx';

            $remotePath = $bucket . '/' . config('filesystems.folder_name.import_product') . '/' . $fileName;
            $localPath = storage_path('app/' . $fileName);

            if (!Storage::disk($diskName)->exists($remotePath)) {
                Log::warning('Import products file not found in bucket: ' . $remotePath);
                return response()->json(
                    [
                        'message' => 'Products xlsx file not found to loaded',
                    ],
                    Response::HTTP_NOT_FOUND,
                );
            }

            $fileContent = Storage::disk($diskName)->get($remotePath);

            file_put_contents($localPath, $fileContent);

            $spreadsheet = IOFactory::load($localPath);
            $sheet = $spreadsheet->getActiveSheet();

            $rows = $sheet->toArray();

            // remove header row
            unset($rows[0]);

            foreach ($rows as $row) {
                $sku = $row[0] ?? null;

                $name = $row[1] ?? null;

                $description = $row[2] ?? null;

                $price = $row[3] ?? null;

                $quantity = $row[4] ?? 0;

                $isActive = $row[5] ?? true;
                if (!$sku || !$name || !$price) {
                    continue;
                }

                Product::updateOrCreate(
                    [
                        'sku' => $sku,
                    ],
                    [
                        'name' => $name,

                        'description' => $description,

                        'price' => $price,

                        'quantity' => $quantity,

                        'is_active' => in_array($isActive, ['כן', 'yes', 'true', true, 1, '1']),

                        'is_deleted' => false,
                    ],
                );
            }

            if (file_exists($localPath)) {
                unlink($localPath);
            }

            return response()->json(
                [
                    'message' => 'Products loaded successfully',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $e) {
            Log::error('Import products from XLSX error: ' . $e->getMessage());
            if (isset($localPath) && file_exists($localPath)) {
                unlink($localPath);
            }
            return response()->json(
                [
                    'message' => 'Failed to loaded product',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function importUsersFromXlsxBucket()
    {
        try {
            $diskName = config('filesystems.storage_service');

            $bucket = config("filesystems.disks.{$diskName}.bucket");

            $fileName = 'users.xlsx';

            $remotePath = $bucket . '/' . config('filesystems.folder_name.import_user') . '/' . $fileName;

            $localPath = storage_path('app/' . $fileName);

            if (!Storage::disk($diskName)->exists($remotePath)) {
                Log::warning('Import users file not found in bucket: ' . $remotePath);
                return response()->json(
                    [
                        'message' => 'Users xlsx file not found to loaded',
                    ],
                    Response::HTTP_NOT_FOUND,
                );
            }

            $fileContent = Storage::disk($diskName)->get($remotePath);

            file_put_contents($localPath, $fileContent);

            $spreadsheet = IOFactory::load($localPath);
            $sheet = $spreadsheet->getActiveSheet();

            $rows = $sheet->toArray();

            // Remove header row
            unset($rows[0]);

            foreach ($rows as $row) {
                $name = $row[0] ?? null;
                $email = $row[1] ?? null;
                $personalId = $row[2] ?? null;
                $phone = $row[3] ?? null;
                $roleId = $row[4] ?? null;

                if (!$name || !$personalId || !$phone || !$roleId || !$email) {
                    continue;
                }
                $role = Role::find($roleId);
                if (!$role) {
                    continue;
                }
                $user = User::updateOrCreate(
                    ['personal_id' => $personalId],
                    [
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => Hash::make($personalId),
                        'is_deleted' => false,
                    ],
                );

                $user->syncRoles([$role]);
            }

            if (file_exists($localPath)) {
                unlink($localPath);
            }

            return response()->json(
                [
                    'message' => 'Users loaded successfully',
                ],
                Response::HTTP_OK,
            );
        } catch (\Throwable $e) {
            Log::error('Import users from XLSX error: ' . $e->getMessage());
            if (isset($localPath) && file_exists($localPath)) {
                unlink($localPath);
            }
            return response()->json(
                [
                    'message' => 'Failed to loaded user',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
    /**
     * Upload a local file to the configured storage disk (S3, MinIO, etc.)
     * and optionally delete it locally after upload.
     *
     * @param string $localPath
     * @param string $remoteDirectory
     * @param bool   $deleteLocal
     * @return int
     */
    public function uploadFileToBucket(string $localPath, string $remoteDirectory = 'logs', bool $deleteLocal = true): int
    {
        try {
            if (!file_exists($localPath)) {
                Log::warning('Warning in LogService: uploadExcelToS3 function: Excel file not found at path:');
                return 200;
            }

            $fileName = basename($localPath);
            $bucket = config('filesystems.disks.minio.bucket');
            $prefixBucket = $bucket . '/';
            $s3Path = $prefixBucket . trim($remoteDirectory, '/') . '/' . $fileName;

            Storage::disk('minio')->put($s3Path, file_get_contents($localPath));

            if ($deleteLocal) {
                unlink($localPath);
            }
            return Response::HTTP_OK;
        } catch (\Exception $e) {
            Log::error('Error in StorageService: uploadFileToBucket function: ' . $e->getMessage());
            return Response::HTTP_INTERNAL_SERVER_ERROR;
        }
    }

    /**
     * Upload an image file to MinIO under <bucket>/images/<generated-name>.
     * Returns a payload compatible with your ImageService expectations.
     *
     * @throws \RuntimeException on failure
     */

    public function uploadImageToBucket(UploadedFile $image, string $directory = 'images'): array|int
    {
        try {
            $disk = config('filesystems.storage_service');

            $extension = strtolower($image->getClientOriginalExtension() ?: 'jpg');
            $originalName = $image->getClientOriginalName();
            $randomFileName = uniqid() . '_' . Str::random(10) . '.' . $extension;

            $bucket = config('filesystems.disks.minio.bucket');
            $directory = $bucket . '/' . $directory;
            $storedPath = $image->storeAs($directory, $randomFileName, $disk);
      
            $imagePath = $storedPath;

            return [
                'status' => Response::HTTP_OK,
                'extension' => $extension,
                'originalName' => $originalName,
                'randomFileName' => $randomFileName,
                'imagePath' => $imagePath,
            ];
        } catch (\Exception $e) {
            Log::error('Error in StorageService: uploadImageToBucket function:' . $e->getMessage());
            $this->logToS3();
        
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    public function deleteImage(string $imagePathOrName)
    {
        try {
            $diskName = config('filesystems.storage_service');
            $bucket = config("filesystems.disks.{$diskName}.bucket");
            $folder = trim(config('filesystems.image_folder', 'images'), '/');
            $diskName = config('filesystems.filesystem_disk');
            $disk = Storage::disk($diskName);

            $bucket = config('filesystems.disks.minio.bucket');

            $fullPath = str_contains($imagePathOrName, '/') ? $imagePathOrName : $bucket . '/' . $folder . '/' . $imagePathOrName;

            if ($disk->exists($fullPath)) {
                return $disk->delete($fullPath);
            }

            Log::info("Image not found: {$fullPath}");

            return false;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->logToS3();
            return Response::HTTP_INTERNAL_SERVER_ERROR;
        }
    }
}
