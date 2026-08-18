# 🏔️ Muncak.Kuy — Website Booking Pendakian Gunung

Website booking pendakian gunung full-stack menggunakan **PHP native + MySQL**, dilengkapi:
- QR Code asli pada E-Tiket (client-side + tertanam di PDF)
- Export tiket & laporan ke Excel (.xls)
- Cetak/unduh E-Tiket sebagai PDF (FPDF)
- Dashboard Admin modern (grafik, kelola booking/gunung/pengguna)
- Toast notification (pengganti `alert()`)
- Animasi premium: awan bergerak, burung terbang, parallax hero, scroll reveal, hover/scale effect

---

## 📁 Struktur Folder

```
muncakkuy/
├── admin/                 # Dashboard & fitur admin
├── assets/
│   ├── css/style.css      # Semua styling & animasi
│   ├── js/main.js         # Interaksi, toast, parallax, scroll reveal
│   └── img/gunung/        # Upload foto gunung ke sini
├── config/db.php          # Konfigurasi koneksi database
├── includes/               # Header, footer, sidebar, functions.php
├── vendor/
│   ├── fpdf/               # Library pembuat PDF
│   └── phpqrcode/          # Library pembuat QR Code
├── database.sql            # Skema + data awal database
├── index.php, login.php, register.php, dashboard.php, search.php,
│   booking.php, process_booking.php, eticket.php, eticket_pdf.php,
│   eticket_excel.php, dst.
└── .htaccess
```

## 🚀 Cara Instalasi di Local (XAMPP / Laragon)

1. Salin folder `muncakkuy` ke dalam `htdocs` (XAMPP) atau `www` (Laragon).
2. Buka **phpMyAdmin**, buat database baru lalu import file `database.sql`
   (atau jalankan: `mysql -u root -p < database.sql`).
3. Buka `config/db.php`, sesuaikan `DB_USER`, `DB_PASS`, dan `DB_NAME` jika berbeda.
4. Set `BASE_URL` di `config/db.php` sesuai folder project, contoh:
   `define('BASE_URL', '/muncakkuy');`
5. Akses `http://localhost/muncakkuy/` di browser.
6. Login admin default:
   - Email: `admin@muncakkuy.com`
   - Password: `admin123`
   - **⚠️ WAJIB diganti setelah login pertama kali (menu Pengaturan)!**

## ☁️ Cara Deploy ke Hosting (cPanel / shared hosting)

1. **Upload file**: Compress folder `muncakkuy` menjadi `.zip`, upload ke `public_html` via File Manager cPanel, lalu ekstrak. (Atau upload manual via FTP/FileZilla.)
2. **Buat database**: Menu *MySQL Databases* di cPanel → buat database & user, lalu berikan *All Privileges*.
3. **Import database**: Buka *phpMyAdmin* → pilih database yang baru dibuat → tab *Import* → pilih file `database.sql`.
4. **Edit konfigurasi**: Ubah `config/db.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'namauser_cpanel');
   define('DB_PASS', 'password_database');
   define('DB_NAME', 'namauser_muncakkuy');
   define('BASE_URL', ''); // kosongkan jika project ada di root domain
   ```
5. **Set permission**: pastikan folder `uploads/` dan `exports/` memiliki permission `755` atau `775`.
6. **Akses domain**: buka `https://namadomainmu.com` — selesai!
7. Ganti password admin default segera setelah live.

## 🐙 Cara Publish ke GitHub

```bash
cd muncakkuy
git init
git add .
git commit -m "Initial commit: Muncak.Kuy booking system"
git branch -M main
git remote add origin https://github.com/USERNAME/muncakkuy.git
git push -u origin main
```

> **Penting**: File `config/db.php` berisi kredensial database. Sebelum push ke
> GitHub publik, sebaiknya buat `config/db.example.php` sebagai contoh, dan
> masukkan `config/db.php` ke `.gitignore` agar kredensial asli tidak ter-upload.
> Untuk deployment, banyak developer memakai *environment variable* atau file
> config terpisah yang di-upload manual ke hosting (tidak lewat git).

### Deploy otomatis dari GitHub ke hosting
Beberapa opsi:
- **cPanel Git Version Control**: cPanel → Git Version Control → Clone repository dari GitHub.
- **GitHub Actions + FTP Deploy**: gunakan action seperti `SamKirkland/FTP-Deploy-Action` untuk auto-upload saat push ke branch `main`.
- **Manual**: `git pull` langsung di server jika hosting mendukung SSH.

## 🔑 Kredensial Contoh (Development)
| Role  | Email                 | Password  |
|-------|------------------------|-----------|
| Admin | admin@muncakkuy.com    | admin123  |

## 🧩 Requirement Server
- PHP 8.0+ (mendukung PDO MySQL, GD Library untuk QR code)
- MySQL 5.7+ / MariaDB 10.3+
- Ekstensi PHP aktif: `pdo_mysql`, `gd`, `mbstring`

## 📚 Dokumentasi
Lihat folder `docs/` untuk:
- **Volume 1** — Analisis Sistem, ERD, Basis Data, Flowchart
- **Volume 2** — Penjelasan Kode PHP, CSS, JavaScript, Cara Kerja Fitur
- **Volume 3** — Panduan Presentasi, Prediksi Pertanyaan Penguji, Tips Demo
