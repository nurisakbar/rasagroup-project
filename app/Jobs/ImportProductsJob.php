<?php

namespace App\Jobs;

use App\Imports\ProductsImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filePath;
    public $batchId;
    public $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $batchId, $userId)
    {
        $this->filePath = $filePath;
        $this->batchId = $batchId;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $fullPath = Storage::path($this->filePath);
            
            if (!file_exists($fullPath)) {
                throw new \Exception("File import tidak ditemukan di path: " . $fullPath);
            }

            Log::info("ImportProductsJob: Starting import for batch {$this->batchId}");

            $import = new ProductsImport($this->batchId, $this->userId);
            Excel::import($import, $fullPath);

            $failures = $import->failures();
            $importedCount = $import->getImportedCount();
            
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }

            $cacheData = Cache::get('import_products_'.$this->batchId, []);
            $cacheData['status'] = count($errorMessages) > 0 ? 'completed_with_errors' : 'completed';
            $cacheData['processed'] = $importedCount;
            $cacheData['message'] = "Berhasil mengupdate {$importedCount} produk.";
            $cacheData['errors'] = $errorMessages;

            Cache::put('import_products_'.$this->batchId, $cacheData, now()->addHours(2));
            Log::info("ImportProductsJob: Completed batch {$this->batchId}");

        } catch (\Exception $e) {
            Log::error("ImportProductsJob: Error in batch {$this->batchId}", [
                'error' => $e->getMessage()
            ]);

            $cacheData = Cache::get('import_products_'.$this->batchId, []);
            $cacheData['status'] = 'failed';
            $cacheData['message'] = "Gagal memproses file: " . $e->getMessage();
            Cache::put('import_products_'.$this->batchId, $cacheData, now()->addHours(2));
        } finally {
            // Hapus file temporary
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
        }
    }
}
