<?php

namespace App\Utilities;

class CryptoUtility
{
    public static function encryptData(string $plaintext, string $password): string
    {
        // CBC mode requires a 16-byte IV; keep salt separate for PBKDF2.
        $salt = random_bytes(16);
        $iv = random_bytes(16);

        $key = hash_pbkdf2('sha256', $password, $salt, 100000, 32, true);

        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($ciphertext === false) {
            return '';
        }

        $binaryBlob = $salt . $iv . $ciphertext;

        return bin2hex($binaryBlob);
    }

    public static function decryptData(string $hexData, string $password): string|false
    {
        $data = hex2bin($hexData);

        if ($data === false || strlen($data) < 32) {
            return false;
        }

        $salt = substr($data, 0, 16);
        $iv = substr($data, 16, 16);
        $ciphertext = substr($data, 32);

        $key = hash_pbkdf2('sha256', $password, $salt, 100000, 32, true);

        return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    }
}
