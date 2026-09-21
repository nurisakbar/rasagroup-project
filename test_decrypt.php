<?php
$signature = "tlADetuS62PZFCtPy612Z+oqcqKDldaW1VquEHoZtolJflW8Jf1rV2+3H9VZnXLtEKzMtisxk3cJwe4ZCjBDTgZxUDqltgfy10f0hzh8FvT2J3cqcJC6rpjY2okhoK7E1KFxcL7jX3C2DN0Sn1B3qwV8Djk/eFu1EubIoKNdJkFJypM0hwSGUz+2cLO5w6IGK+Awimc7IhGoIAUjvYANMGSP3h5xtNOmTPMf+55iztezEFNYABL9tOafSADWGrg6Z3Nj7psbxOajp3jbNaTnunFob8UG10qcxzXIZ3zos0k0QT+VcK3A6/ZqJpMYWF8bWtOMak62loRwUbaYWJCadQ==";
$path = "/Applications/MAMP/htdocs/rasagroup/rasagroup-project/37020_server.crt";
$pub = openssl_pkey_get_public(file_get_contents($path));
$decrypted = "";
$res = openssl_public_decrypt(base64_decode($signature), $decrypted, $pub);
if ($res) {
    echo "Decrypted successfully!\n";
    echo bin2hex($decrypted) . "\n";
} else {
    echo "Failed to decrypt:\n";
    while ($msg = openssl_error_string()) {
        echo $msg . "\n";
    }
}
