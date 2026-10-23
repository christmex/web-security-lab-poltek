# Lab Keamanan Web — Branch 1: IDOR Publik

> **PERINGATAN:** Aplikasi ini sengaja dibuat rentan untuk tujuan edukasi.
> Jangan pernah deploy ke internet publik dan jangan tiru polanya di produksi.

## Setup

```bash
composer install
npm install
cp .env.example .env   # jika .env belum ada
php artisan key:generate
php artisan migrate:fresh --seed   # buat tabel + 3 user demo
npm run dev            # terminal terpisah
php artisan serve      # atau pakai Herd: http://web-security-lab-poltek.test
```

User demo (password semua: `password`):

| id | email                | peran   |
|----|----------------------|---------|
| 1  | attacker@example.com | penyerang |
| 2  | victim1@example.com  | korban  |
| 3  | victim2@example.com  | korban  |

## Kerentanan

Endpoint profil **publik, tanpa autentikasi**:

```
GET /api/profile/{id}
```

Dua celah sekaligus:

1. **IDOR** — `{id}` diambil mentah dari URL. Ganti angkanya (`/1`, `/2`, `/3`, ...)
   untuk membaca user mana pun.
2. **Sensitive data exposure** — respons membuang **semua** kolom, termasuk
   hash password dan **token reset password dalam bentuk plaintext**.

## Alur demo

1. Korban (atau siapa pun) minta reset password:
   - Buka `/forgot-password`, isi `victim1@example.com`.
   - Token plaintext tersimpan di baris user (lihat `User::sendPasswordResetNotification`).
2. Penyerang membaca token korban tanpa login:
   ```bash
   curl http://localhost:8000/api/profile/2
   ```
   Respons memuat field `password_reset_token`.
3. Penyerang pakai token itu untuk mengambil alih akun:
   - Buka `/reset-password/{token}?email=victim1@example.com`, set password baru.
   - Akun korban kini milik penyerang.

## File yang relevan

- `routes/web.php` — pendaftaran route `api.profile.show`
- `app/Http/Controllers/Api/PublicProfileController.php` — controller rentan
- `app/Models/User.php` — mirror token reset ke kolom plaintext
- `tests/Feature/SecurityLab/PublicProfileIdorTest.php` — bukti eksploit

## Perbaikan (yang seharusnya dilakukan)

- Wajibkan autentikasi + otorisasi (user hanya boleh baca profilnya sendiri).
- Jangan pernah kirim hash password / token reset di respons API.
- Simpan token reset dalam bentuk hash, bukan plaintext (default Laravel).

Branch berikutnya (`branch-2-auth-idor`) menambahkan proteksi login — tapi IDOR tetap ada.
