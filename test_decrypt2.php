<?php
$signature = "cZ2L7+Hmst0EnbnnnnAeHSf78bl2D0x3pbSE+xKp3EKoWkbdNWkuhLYtoTQ1NjG5fen8cMXsl5YIrBzQTeB5kmcYVPRcrmescdZ3YpuQ5iIOH2AzZp2qZTHi44WQR1fQhCBdvlSwsJVJ81D1vC+lqv4Jwioy5KZevTGXcDHfaSGyZpN1D8dJyTEW8Ls+s3w40F5V4/zSc4I1Ee8iG8K0D/+w723dKcb4ZewoSDGZ76WdDEap7XdG0lgyXNkUjUa1rrYzQSl1dTcI4St3CRW1fbLx1MRvEaZl9xoVjHMf/K1n8KXigDJcYpZIVS9j2PX/ZNTNLKvHRIu9UjV2xJTNLw==";
$keys = glob("/Applications/MAMP/htdocs/rasagroup/rasagroup-project/storage/app/*.crt") + 
        glob("/Applications/MAMP/htdocs/rasagroup/rasagroup-project/storage/app/*.pem") + 
        glob("/Applications/MAMP/htdocs/rasagroup/rasagroup-project/*.pem") +
        glob("/Applications/MAMP/htdocs/rasagroup/rasagroup-project/storage/app/faspay/rdi/*.crt");

foreach ($keys as $path) {
    if (!file_exists($path)) continue;
    $pub = openssl_pkey_get_public(file_get_contents($path));
    if (!$pub) continue;
    
    $decrypted = "";
    $res = openssl_public_decrypt(base64_decode($signature), $decrypted, $pub);
    if ($res) {
        echo "Decrypted successfully with " . basename($path) . "!\n";
    }
}
echo "Done checking all keys.\n";
