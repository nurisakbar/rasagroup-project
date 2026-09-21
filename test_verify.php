<?php
$stringToSign = "POST:/v1.0/transfer-va/inquiry:43bf9d9af609cbd6e152cc0888fe3156c11342b0cffbd70baa02cb910d3e1e8e:2026-09-10T16:41:13+07:00";
$signature = "vj1YTuNUAj3U2lhV7dQqcOMcZR0anIejkkvRzG9ImET4pVzgb7OVyhvOg/x6SfIXOqR42SnFvIbEv/maeM8No8HF68ggYuoWvZ3gJcTy9sPZqBKYWfrjI0bZl0EUOfsUGWoK44+RqTRFPyFDnVQLYDeqyL5dYBtSIWfB+D6G79CEt8N7kN2zo8ru7XUl+54tiU8DL/8wpY0EER8wt2+CVipjnIE4fODRdT36vUCPB1Vloguil8gwUOqk5OLrmJ4l/gQyX4TGsaWz1lCRYLxScmMS6OV2+75TTG+iWEBXznmfZmV2lmJdeBYnDvZYgR/yvhkikRtOtKaIdAPwL0ACKg==";
$publicKey = openssl_pkey_get_public(file_get_contents("storage/app/faspay_public_key.pem"));
$verifyResult = openssl_verify($stringToSign, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256);
echo "Verify with faspay_public_key.pem: " . $verifyResult . " - " . openssl_error_string() . "\n";

$publicKey2 = openssl_pkey_get_public(file_get_contents("storage/app/faspay_public_key_dev.pem"));
$verifyResult2 = openssl_verify($stringToSign, base64_decode($signature), $publicKey2, OPENSSL_ALGO_SHA256);
echo "Verify with faspay_public_key_dev.pem: " . $verifyResult2 . " - " . openssl_error_string() . "\n";
