<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ActiveProductsImport implements ToModel, WithHeadingRow
{
    private $brandCache = [];
    private $categoryCache = [];

    public function model(array $row)
    {
        if (empty($row['product_id'])) {
            return null;
        }

        $product = Product::find($row['product_id']);
        if (!$product) {
            return null;
        }

        $updateData = [];

        if (isset($row['kode_produk_sku'])) {
            $updateData['code'] = $row['kode_produk_sku'];
        }
        if (isset($row['nama_komersial'])) {
            $updateData['commercial_name'] = $row['nama_komersial'];
        }
        
        if (!empty($row['brand'])) {
            $updateData['brand_id'] = $this->getOrCreateBrand($row['brand']);
        }
        if (!empty($row['kategori'])) {
            $updateData['category_id'] = $this->getOrCreateCategory($row['kategori']);
        }

        if (isset($row['status'])) {
            $status = strtolower(trim($row['status']));
            if ($status === 'aktif') $updateData['status'] = 'active';
            elseif ($status === 'non-aktif') $updateData['status'] = 'inactive';
            else $updateData['status'] = $status;
        }

        if (isset($row['harga_jual'])) {
            $updateData['price'] = $this->parseNumber($row['harga_jual']);
        }

        if (isset($row['poin_per_unit'])) {
            $updateData['reseller_point'] = $this->parseNumber($row['poin_per_unit']);
        }

        if (isset($row['berat_gram'])) {
            $updateData['weight'] = $this->parseNumber($row['berat_gram']);
        }

        if (array_key_exists('satuan_uom', $row)) {
            $updateData['unit'] = $row['satuan_uom'];
        }

        if (array_key_exists('ukuran_sizing', $row)) {
            $updateData['size'] = $row['ukuran_sizing'];
        }

        if (array_key_exists('satuan_besar', $row)) {
            $updateData['large_unit'] = $row['satuan_besar'];
        }

        if (array_key_exists('isi_per_satuan_besar', $row)) {
            $updateData['units_per_large'] = $row['isi_per_satuan_besar'] ? $this->parseNumber($row['isi_per_satuan_besar']) : null;
        }

        if (array_key_exists('deskripsi', $row)) {
            $updateData['description'] = $row['deskripsi'];
        }

        if (!empty($updateData)) {
            $product->update($updateData);
        }

        return null;
    }

    private function getOrCreateBrand(string $name): string
    {
        $name = trim($name);
        $cacheKey = strtolower($name);

        if (isset($this->brandCache[$cacheKey])) {
            return $this->brandCache[$cacheKey];
        }

        $brand = Brand::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name), 'is_active' => true]
        );

        $this->brandCache[$cacheKey] = $brand->id;
        return $brand->id;
    }

    private function getOrCreateCategory(string $name): string
    {
        $name = trim($name);
        $cacheKey = strtolower($name);

        if (isset($this->categoryCache[$cacheKey])) {
            return $this->categoryCache[$cacheKey];
        }

        $category = Category::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name), 'is_active' => true]
        );

        $this->categoryCache[$cacheKey] = $category->id;
        return $category->id;
    }

    private function parseNumber($val)
    {
        if (is_numeric($val)) return (float) $val;
        $val = preg_replace('/[^0-9.,]/', '', $val);
        $val = str_replace(',', '', $val);
        return (float) $val;
    }
}
