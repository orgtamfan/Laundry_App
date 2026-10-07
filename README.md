# Rumah Laundry

## Menjalankan secara lokal dengan Laragon

1. Jalankan Apache dan MySQL dari Laragon.
2. Buat database bernama `laundry` dan import file `laundry.sql` dari folder ini.
3. Pastikan koneksi di `app/includes/_functions.php` sesuai dengan akun MySQL Laragon Anda. Nilai bawaan memakai `root` tanpa password.
4. Dari terminal, jalankan frontend:

   ```powershell
   cd frontend
   npm install
   npm run dev
   ```

5. Buka `http://localhost:5173/`. Vite meneruskan permintaan API ke aplikasi PHP di `http://localhost/Laundry_App/app/api/`.

## Akun contoh

- Administrator: `admin` / `admin123`
- Pengguna: `user` / `user123`

Login, data order, paket, akun karyawan, pembayaran, dan riwayat transaksi menggunakan sesi PHP dan database MySQL yang sama. Pengelolaan paket dan akun karyawan hanya tersedia untuk Administrator.

## Build produksi

Jalankan `npm run build` dari folder `frontend`. Hasil build berada di `frontend/dist`. Jika folder proyek atau path backend berbeda, sesuaikan path API produksi di `frontend/src/api.js`.
