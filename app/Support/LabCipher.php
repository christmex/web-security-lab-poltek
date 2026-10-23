<?php

namespace App\Support;

/**
 * SECURITY LAB — BRANCH 3 helper.
 *
 * Turns a user id into the opaque URL identifier and back, using the salt as
 * the only key material. The scheme is deterministic and fully reproducible in
 * any language, so a student who has recovered the salt (from branch 2) can
 * forge the identifier for any id:
 *
 *   key        = sha256(salt)                 (32 raw bytes, AES-256)
 *   iv         = first 16 bytes of sha256(salt)
 *   cipher     = AES-256-CBC(plaintext = id, key, iv)
 *   identifier = base64url(cipher)
 *
 * A deterministic IV is deliberately weak — fine for a teaching lab, never for
 * production.
 */
class LabCipher
{
    private const CIPHER = 'aes-256-cbc';

    private string $key;

    private string $iv;

    public function __construct(?string $salt = null)
    {
        $salt = $salt ?? config('lab.salt');
        $digest = hash('sha256', $salt, true);

        $this->key = $digest;
        $this->iv = substr($digest, 0, 16);
    }

    public function encryptId(int|string $id): string
    {
        $cipherText = openssl_encrypt((string) $id, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $this->iv);

        return rtrim(strtr(base64_encode($cipherText), '+/', '-_'), '=');
    }

    /**
     * Returns the decrypted id, or null when the identifier is not valid.
     */
    public function decryptId(string $identifier): ?string
    {
        $base64 = strtr($identifier, '-_', '+/');
        $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);

        $cipherText = base64_decode($base64, true);

        if ($cipherText === false) {
            return null;
        }

        $plain = openssl_decrypt($cipherText, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $this->iv);

        return $plain === false ? null : $plain;
    }
}
