<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Support\Facades\Cache;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, WithEvents
{
    use SkipsFailures;

    private $importedCount = 0;
    private $skippedCount = 0;
    private $brandCache = [];
    private $categoryCache = [];
    private $batchId;
    private $userId;

    public function __construct($batchId = null, $userId = null)
    {
        $this->batchId = $batchId;
        $this->userId = $userId;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function(BeforeImport $event) {
                if ($this->batchId) {
                    $totalRows = array_sum($event->getReader()->getTotalRows());
                    // Subtract header row if there is one (TotalRows usually includes header)
                    $totalRows = max(0, $totalRows - 1); 
                    
                    Cache::put('import_products_'.$this->batchId, [
                        'status' => 'processing',
                        'total' => $totalRows,
                        'processed' => 0,
                        'message' => 'Processing...',
                        'errors' => []
                    ], now()->addHours(2));
                }
            }
        ];
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            Log::info('ProductsImport: Processing row', [
                'row_data' => $row,
                'row_keys' => array_keys($row),
            ]);

            // Check if product with same code already exists
            $existingProduct = null;
            if (!empty($row['product_code'])) {
                $existingProduct = Product::where('code', $row['product_code'])->first();
            }

            if ($existingProduct) {
                // Update existing product instead of skipping
                Log::info('ProductsImport: Updating existing product', [
                    'code' => $row['product_code'],
                    'existing_id' => $existingProduct->id,
                ]);
                
                // Get or create brand
                $brandId = null;
                if (!empty($row['brand'])) {
                    $brandId = $this->getOrCreateBrand($row['brand']);
                }

                // Get or create category
                $categoryId = null;
                if (!empty($row['category'])) {
                    $categoryId = $this->getOrCreateCategory($row['category']);
                }

                // Convert weight and price conditionally
                $updateData = [];

                if (isset($row['product_name'])) {
                    $updateData['name'] = $row['product_name'];
                } elseif (isset($row['description']) || isset($row['commercial_name'])) {
                    $updateData['name'] = $row['description'] ?? $row['commercial_name'];
                }

                if (isset($row['commercial_name'])) {
                    $updateData['commercial_name'] = $row['commercial_name'];
                }

                if (isset($row['description_2']) || isset($row['description'])) {
                    $updateData['description'] = $row['description_2'] ?? $row['description'];
                }

                if (isset($row['description_2'])) {
                    $updateData['technical_description'] = $row['description_2'];
                }

                if ($brandId) {
                    $updateData['brand_id'] = $brandId;
                }

                if ($categoryId) {
                    $updateData['category_id'] = $categoryId;
                }

                if (isset($row['size'])) {
                    $updateData['size'] = $row['size'];
                    $updateData['weight'] = $this->parseWeight($row['size']);
                }

                if (isset($row['um'])) {
                    $updateData['unit'] = $row['um'];
                }

                if (isset($row['price'])) {
                    $updateData['price'] = $this->parsePrice($row['price']);
                }

                if (isset($row['reseller_point'])) {
                    $updateData['reseller_point'] = (int)$row['reseller_point'];
                }

                // Update existing product if there is something to update
                if (!empty($updateData)) {
                    $existingProduct->update($updateData);
                }

                Log::info('ProductsImport: Product updated successfully', [
                    'product_id' => $existingProduct->id,
                    'product_code' => $existingProduct->code,
                ]);

                $this->importedCount++;
                $this->updateProgress();
                return null; // Return null because we're updating, not creating new model
            }

            $this->importedCount++;

            // Get or create brand
            $brandId = null;
            if (!empty($row['brand'])) {
                $brandId = $this->getOrCreateBrand($row['brand']);
                Log::info('ProductsImport: Brand processed', [
                    'brand_name' => $row['brand'],
                    'brand_id' => $brandId,
                ]);
            }

            // Get or create category
            $categoryId = null;
            if (!empty($row['category'])) {
                $categoryId = $this->getOrCreateCategory($row['category']);
                Log::info('ProductsImport: Category processed', [
                    'category_name' => $row['category'],
                    'category_id' => $categoryId,
                ]);
            }

            // Convert weight from unit string (e.g., "1 L" -> 1000g, "500 ML" -> 500g)
            $weight = $this->parseWeight($row['size'] ?? null);
            $price = $this->parsePrice($row['price'] ?? 0);
            $resellerPoint = (int)($row['reseller_point'] ?? 0);

            $productData = [
                'id' => (string) Str::uuid(),
                'code' => $row['product_code'] ?? null,
                'name' => $row['product_name'] ?? $row['description'] ?? $row['commercial_name'] ?? 'Unnamed Product',
                'commercial_name' => $row['commercial_name'] ?? null,
                'description' => $row['description_2'] ?? $row['description'] ?? null,
                'technical_description' => $row['description_2'] ?? null,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'size' => $row['size'] ?? null,
                'unit' => $row['um'] ?? null,
                'price' => $price,
                'reseller_point' => $resellerPoint,
                'weight' => $weight,
                'status' => 'active',
                'created_by' => $this->userId ?: Auth::id(),
            ];

            Log::info('ProductsImport: Creating product', [
                'product_data' => $productData,
            ]);

            $product = new Product($productData);
            
            Log::info('ProductsImport: Product model created successfully', [
                'product_id' => $product->id,
                'product_code' => $product->code,
                'product_name' => $product->name,
            ]);

            $this->updateProgress();

            return $product;
        } catch (\Exception $e) {
            Log::error('ProductsImport: Error processing row', [
                'row_data' => $row,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get or create brand by name
     */
    private function getOrCreateBrand(string $name): string
    {
        try {
            $name = trim($name);
            $cacheKey = strtolower($name);

            if (isset($this->brandCache[$cacheKey])) {
                Log::debug('ProductsImport: Brand found in cache', [
                    'name' => $name,
                    'brand_id' => $this->brandCache[$cacheKey],
                ]);
                return $this->brandCache[$cacheKey];
            }

            $brand = Brand::firstOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name),
                    'is_active' => true,
                ]
            );

            $wasRecentlyCreated = $brand->wasRecentlyCreated;
            Log::info('ProductsImport: Brand processed', [
                'name' => $name,
                'brand_id' => $brand->id,
                'was_created' => $wasRecentlyCreated,
            ]);

            $this->brandCache[$cacheKey] = $brand->id;
            return $brand->id;
        } catch (\Exception $e) {
            Log::error('ProductsImport: Error getting/creating brand', [
                'name' => $name,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get or create category by name
     */
    private function getOrCreateCategory(string $name): string
    {
        try {
            $name = trim($name);
            $cacheKey = strtolower($name);

            if (isset($this->categoryCache[$cacheKey])) {
                Log::debug('ProductsImport: Category found in cache', [
                    'name' => $name,
                    'category_id' => $this->categoryCache[$cacheKey],
                ]);
                return $this->categoryCache[$cacheKey];
            }

            $category = Category::firstOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name),
                    'is_active' => true,
                ]
            );

            $wasRecentlyCreated = $category->wasRecentlyCreated;
            Log::info('ProductsImport: Category processed', [
                'name' => $name,
                'category_id' => $category->id,
                'was_created' => $wasRecentlyCreated,
            ]);

            $this->categoryCache[$cacheKey] = $category->id;
            return $category->id;
        } catch (\Exception $e) {
            Log::error('ProductsImport: Error getting/creating category', [
                'name' => $name,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Parse price from various formats
     */
    private function parsePrice($price): float
    {
        try {
            $originalPrice = $price;
            
            if (is_numeric($price)) {
                $parsed = (float) $price;
                Log::debug('ProductsImport: Price parsed (numeric)', [
                    'original' => $originalPrice,
                    'parsed' => $parsed,
                ]);
                return $parsed;
            }
            
            // Remove currency symbols and formatting
            $price = preg_replace('/[^0-9.,]/', '', $price);
            $price = str_replace(',', '', $price);
            
            $parsed = (float) $price;
            Log::debug('ProductsImport: Price parsed (formatted)', [
                'original' => $originalPrice,
                'parsed' => $parsed,
            ]);
            
            return $parsed;
        } catch (\Exception $e) {
            Log::error('ProductsImport: Error parsing price', [
                'price' => $price,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Parse weight from size string
     */
    private function parseWeight(?string $size): int
    {
        try {
            if (empty($size)) {
                Log::debug('ProductsImport: Weight parsed (default)', [
                    'size' => $size,
                    'weight' => 500,
                ]);
                return 500; // Default 500 gram
            }

            $originalSize = $size;
            $size = strtoupper(trim($size));
            
            // Extract number and unit
            if (preg_match('/(\d+(?:\.\d+)?)\s*(L|ML|KG|G|GR|GRAM)?/i', $size, $matches)) {
                $value = (float) $matches[1];
                $unit = $matches[2] ?? '';
                
                $weight = 500; // Default
                switch (strtoupper($unit)) {
                    case 'L':
                        $weight = (int) ($value * 1000); // 1 L = 1000g (approx)
                        break;
                    case 'ML':
                        $weight = (int) $value; // 1 ML ≈ 1g
                        break;
                    case 'KG':
                        $weight = (int) ($value * 1000);
                        break;
                    case 'G':
                    case 'GR':
                    case 'GRAM':
                        $weight = (int) $value;
                        break;
                    default:
                        $weight = (int) ($value * 1000); // Assume liters if no unit
                }
                
                Log::debug('ProductsImport: Weight parsed', [
                    'original_size' => $originalSize,
                    'value' => $value,
                    'unit' => $unit,
                    'weight' => $weight,
                ]);
                
                return $weight;
            }

            Log::debug('ProductsImport: Weight parsed (default - no match)', [
                'size' => $originalSize,
                'weight' => 500,
            ]);
            return 500; // Default
        } catch (\Exception $e) {
            Log::error('ProductsImport: Error parsing weight', [
                'size' => $size,
                'error' => $e->getMessage(),
            ]);
            return 500;
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'product_code' => 'nullable|string|max:50',
            'product_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'description_2' => 'nullable|string|max:500',
            'commercial_name' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'um' => 'nullable|string|max:20',
            'price' => 'nullable',
            'reseller_point' => 'nullable|integer|min:0',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'price.required' => 'Harga (Price) wajib diisi',
        ];
    }

    /**
     * @return int
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * @return int
     */
    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    private function updateProgress()
    {
        if ($this->batchId && ($this->importedCount % 10 === 0)) {
            $data = Cache::get('import_products_'.$this->batchId);
            if ($data) {
                $data['processed'] = $this->importedCount;
                Cache::put('import_products_'.$this->batchId, $data, now()->addHours(2));
            }
        }
    }
}
