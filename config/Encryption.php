<?php
/**
 * Encryption.php
 * Handles AES-256-CBC encryption/decryption for sensitive row data
 * (phone numbers, addresses, etc.) before it is persisted to the database,
 * as required by the assignment's security specification.
 *
 * Key management: the secret key + IV base is stored in a server-side
 * constant (never in the database, never in client-side code, never in git).
 * In production, load this from an environment variable instead.
 */
class Encryption
{
    private static string $method = 'aes-256-cbc';

    // IMPORTANT: change this key before deployment and keep it secret
    // (e.g. move to an environment variable: getenv('THESPARKS_SECRET_KEY'))
    private static string $secretKey = 'TheSparks#2026-ChangeThisSecretKey!!';

    /**
     * Encrypts plain text. A random IV is generated per call and prepended
     * (base64-encoded) to the ciphertext so it can be recovered on decrypt.
     */
    public static function encrypt(string $plainText): string
    {
        $ivLength = openssl_cipher_iv_length(self::$method);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $key = hash('sha256', self::$secretKey, true);
        $cipherText = openssl_encrypt($plainText, self::$method, $key, OPENSSL_RAW_DATA, $iv);

        // store iv + ciphertext together, base64 encoded for safe DB storage
        return base64_encode($iv . $cipherText);
    }

    public static function decrypt(?string $encoded): string
    {
        if (empty($encoded)) {
            return '';
        }
        $raw = base64_decode($encoded);
        $ivLength = openssl_cipher_iv_length(self::$method);
        $iv = substr($raw, 0, $ivLength);
        $cipherText = substr($raw, $ivLength);

        $key = hash('sha256', self::$secretKey, true);
        $decrypted = openssl_decrypt($cipherText, self::$method, $key, OPENSSL_RAW_DATA, $iv);

        return $decrypted === false ? '' : $decrypted;
    }
}
