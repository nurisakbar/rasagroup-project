<?php
$publicKeyPath = '/Applications/MAMP/htdocs/rasagroup/UAT OK/faspay_public_key.pem';
$publicKey = openssl_pkey_get_public(file_get_contents($publicKeyPath));

$signature = "gnLLY/HaGFoGPSmGpmfluYM4/jnGi2b37yin1Rn6/4ofnfUelRorieQZfdoDIWm9taV8P/TwWHVcCNkX0klqTfkdvF+U//ksTaLHJZv4qW7DUtIlgDEp2gp3OozkrCadTwK/tDxCxgbAV5Ri6Z19ATd3ySirxIVv2M3rtvsips9wf+iyhEDYjUtAfKeAFttuSW21EsO6+/LBGdizpUw6Hjav67xG36xQ9lbDvtQ0tQ3BVcOH/JLJmftZPqpuVQjXSw+aJknzn9yHrmYDGmT8FzLjdfK7FnqVCER/hkq067cRkxyskasWRDP37BpWyzp9jUAyJ7PMy8WdsxquGokZcA==";

$arr = [
    "partnerServiceId" => "370201",
    "customerNo" => "0260806001",
    "virtualAccountNo" => "3702010260806001",
    "inquiryRequestId" => "10d854a2-fad5-4aeb-8942-196d3cc5"
];

function pc_permute($items, $perms = []) {
    if (empty($items)) { 
        yield $perms;
    } else {
        for ($i = count($items) - 1; $i >= 0; --$i) {
             $newitems = $items;
             $newperms = $perms;
             list($foo) = array_splice($newitems, $i, 1);
             array_unshift($newperms, $foo);
             yield from pc_permute($newitems, $newperms);
        }
    }
}

$keys = array_keys($arr);
$permutations = pc_permute($keys);

$timestamp = "2026-08-06T11:54:08+07:00";
$method = "POST";
$path = "/api/faspay/snap/inquiry";

foreach ($permutations as $perm) {
    $newArr = [];
    foreach ($perm as $k) {
        $newArr[$k] = $arr[$k];
    }
    
    // JSON variants
    $variants = [
        json_encode($newArr),
        json_encode($newArr, JSON_UNESCAPED_SLASHES),
        str_replace('":"', '": "', json_encode($newArr)),
        str_replace(',', ', ', json_encode($newArr))
    ];

    foreach ($variants as $bodyRaw) {
        $bodyHashLower = strtolower(hash('sha256', $bodyRaw));
        
        $formats = [
            "{$method}:{$path}:{$bodyHashLower}:{$timestamp}",
            "{$method}:{$path}::{$bodyHashLower}:{$timestamp}",
            "{$method}:{$path}:77001:{$bodyHashLower}:{$timestamp}",
            "{$method}:{$path}:37020:{$bodyHashLower}:{$timestamp}"
        ];
        
        foreach ($formats as $str) {
            if (openssl_verify($str, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256) === 1) {
                echo "MATCH FOUND!\n$str\nBody: $bodyRaw\n"; 
                exit;
            }
        }
    }
}
echo "No JSON permutations matched.\n";
