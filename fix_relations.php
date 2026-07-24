<?php
$files = [
    "app/Http/Controllers/Admin/WarehouseController.php",
    "app/Http/Controllers/Admin/OrderController.php",
    "app/Http/Controllers/Admin/DistributorController.php",
    "app/Http/Controllers/HubController.php",
    "app/Http/Controllers/Distributor/OrderController.php",
    "app/Http/Controllers/Distributor/ManageOrderController.php",
    "app/Http/Controllers/Api/OrderApiController.php",
    "app/Http/Controllers/Api/WarehouseApiController.php",
    "app/Http/Controllers/CartController.php",
    "app/Http/Controllers/Buyer/AddressController.php",
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Arrays in with()
    $content = preg_replace("/with\(\[\s*'province'\s*,\s*'regency'\s*,\s*'district'\s*,\s*'village'\s*\]\)/", "with(['wilayah'])", $content);
    $content = preg_replace("/with\(\[\s*'province'\s*,\s*'regency'\s*,\s*'district'\s*\]\)/", "with(['wilayah'])", $content);
    $content = preg_replace("/with\(\[\s*'province'\s*,\s*'regency'\s*\]\)/", "with(['wilayah'])", $content);

    // Specific warehouse/sourceWarehouse combinations
    $content = preg_replace("/'warehouse\.province'\s*,\s*'warehouse\.regency'/", "'warehouse.wilayah'", $content);
    $content = preg_replace("/'sourceWarehouse\.province'\s*,\s*'sourceWarehouse\.regency'/", "'sourceWarehouse.wilayah'", $content);
    
    // Any remaining eager loads that just have one of them inside an array
    $content = preg_replace("/'warehouse\.province'/", "'warehouse.wilayah'", $content);
    $content = preg_replace("/'sourceWarehouse\.province'/", "'sourceWarehouse.wilayah'", $content);
    $content = preg_replace("/'province'/", "'wilayah'", $content); // Be careful with this, only if in with()
    
    file_put_contents($file, $content);
}
echo "Done replacing relations.\n";
