# Lab Keamanan Web — Branch 2: IDOR di Balik Login

> **PERINGATAN:** Aplikasi ini sengaja dibuat rentan untuk tujuan edukasi.
> Jangan pernah deploy ke internet publik dan jangan tiru polanya di produksi.

## Setup

```bash
composer install
npm install
php artisan key:generate
php artisan migrate:fresh --seed   # buat tabel + 3 user demo
npm run dev            # terminal terpisah
php artisan serve
```

User demo (password semua: `password`): `attacker@example.com` (id 1),
`victim1@example.com` (id 2), `victim2@example.com` (id 3).

## Kerentanan

```
GET /api/profile/{id}        (butuh login)
```

Bedanya dengan branch 1: sekarang **wajib login**. Tapi itu **satu-satunya**
proteksi — tidak ada otorisasi per-objek. Jadi:

1. **IDOR masih ada** — penyerang yang sudah login bisa ganti `{id}` dan membaca
   akun siapa pun, bukan hanya akunnya sendiri.
2. Respons tetap membocorkan token reset plaintext **dan** menambah satu field
   baru: `salt` berisi `Y0uM16htN33dM3`.

Field `salt` itu adalah umpan untuk branch 3.

## Alur demo

1. Login sebagai penyerang (`attacker@example.com`).
2. Dari sesi yang login, baca akun korban:
   ```bash
   # pakai cookie sesi dari browser yang sudah login
   curl -b "cookie_sesi_anda" http://localhost:8000/api/profile/2
   ```
   Respons: data korban + `"salt": "Y0uM16htN33dM3"`.
3. Tanpa login → `401 Unauthorized`.
4. Ambil-alih akun sama seperti branch 1 (lewat token reset yang bocor).

## File yang relevan

- `config/lab.php` — nilai `salt`
- `routes/web.php` — route di balik middleware `auth`
- `app/Http/Controllers/Api/AuthenticatedProfileController.php`
- `tests/Feature/SecurityLab/AuthenticatedProfileIdorTest.php`

## Perbaikan (yang seharusnya dilakukan)

- Cek login **dan** otorisasi: user hanya boleh akses profilnya sendiri
  (mis. `$request->user()->id === $id`, atau Policy).
- Jangan bocorkan hash password / token reset / rahasia internal.

Branch berikutnya (`branch-3-encrypted-idor`) mengganti id angka dengan id
terenkripsi memakai `salt` ini — tantangan untuk peserta.
