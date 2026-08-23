<?php
$file = '/Applications/MAMP/htdocs/rasagroup/rasagroup-project/app/Http/Controllers/Auth/RegisteredUserController.php';
$content = file_get_contents($file);

// Replace the hardcoded 'role' => 'buyer' logic
$search = "'role' => 'buyer',";
$replace = "'role' => !empty(\$request->sales_code) ? 'outlet' : 'buyer',";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Role logic updated in RegisteredUserController.\n";
