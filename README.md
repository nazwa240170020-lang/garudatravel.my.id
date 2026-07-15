# Garuda - Sistem Pemesanan Tiket Pesawat

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version" />
  <img src="https://img.shields.io/badge/Laravel-13.8-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Version" />
  <img src="https://img.shields.io/badge/Livewire-4.3-4e56a6?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire Version" />
  <img src="https://img.shields.io/badge/Filament-5.6-f3c042?style=for-the-badge&logo=laravel&logoColor=white" alt="Filament Version" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.1-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS Version" />
  <img src="https://img.shields.io/badge/Vite-8.0-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite Version" />
</p>

Garuda adalah aplikasi web sistem pemesanan tiket pesawat berbasis **Laravel 13** yang dirancang dengan arsitektur modern, aman, dan berkinerja tinggi. Proyek ini ditujukan untuk memenuhi tugas **Ujian Akhir Semester (UAS)**.

Sistem ini mencakup seluruh alur pemesanan tiket dari pencarian penerbangan, pemilihan kelas/kursi secara real-time, integrasi payment gateway **Midtrans**, pengiriman Boarding Pass otomatis berformat PDF via Email, hingga Panel Admin menggunakan **Filament v3** untuk pengelolaan data.

---

## Spesifikasi Stack Teknologi

Berdasarkan berkas konfigurasi sistem (`composer.json` dan `package.json`), berikut adalah rincian stack teknologi yang digunakan:

### Backend & Core
*   **PHP:** `^8.3`
*   **Laravel Framework:** `^13.8`
*   **Livewire:** `^4.3`
*   **Filament Admin:** `5.6`
*   **Scribe API Generator:** `^5.8`
*   **Midtrans PHP SDK:** `^2.6`
*   **Laravel DomPDF:** `^3.1`
*   **Simple QR Code:** `^4.2`

### Frontend & Build Tools
*   **Vite:** `^8.0.0`
*   **TailwindCSS:** `^3.1.0` (Dev: `@tailwindcss/vite ^4.0.0` & `@tailwindcss/forms ^0.5.2`)
*   **Alpine.js:** `^3.4.2`
*   **Concurrently:** `^9.0.1`

---

## Fitur Utama Aplikasi

### Sisi Pengguna (Customer)
1.  **Pencarian & Filter Penerbangan:** Pencarian berdasarkan bandara keberangkatan, bandara tujuan, tanggal penerbangan, kelas penerbangan, dan jumlah penumpang.
2.  **Pemilihan Kelas Penerbangan (Tiering):** Pilihan kelas Ekonomi dan Bisnis dengan harga dan fasilitas dinamis.
3.  **Peta Kursi Interaktif (Visual Grid):** Memilih nomor kursi secara visual dengan pembaruan dinamis.
4.  **Validasi Data Penumpang:** Formulir booking terintegrasi dengan validasi kewarganegaraan, tanggal lahir, dan nama lengkap.
5.  **Sistem Kode Promo:** Diskon otomatis menggunakan kode promo (baik tipe persentase maupun potongan nominal langsung).
6.  **Payment Gateway Midtrans:** Integrasi pembayaran menggunakan Midtrans Snap (Gopay, QRIS, Virtual Account, Credit Card) serta Transfer Bank Manual.
7.  **E-Ticket & Boarding Pass PDF:** Setelah pembayaran sukses, sistem merender berkas PDF Boarding Pass dan mengirimkannya langsung ke email pembeli.

### Sisi Admin (Admin Panel)
Menggunakan **Filament v3** yang kaya fitur untuk:
*   Mengelola Data Bandara (Airport) & Maskapai (Airline).
*   Mengelola Jadwal Penerbangan (Flight) & Segmen Transit (Flight Segment).
*   Mengelola Kursi Penerbangan & Fasilitas Kelas.
*   Mengelola Kode Promo (Promo Codes) & Monitoring Transaksi Booking.

---

## Arsitektur, Perbaikan, & Optimasi Keamanan

1.  **Otorisasi Keamanan Tingkat Tinggi (Auth Protection):**
    *   Implementasi `TransactionPolicy` dengan `Gate::authorize()` pada alur pembayaran dan detail transaksi.
    *   Mencegah celah keamanan *Booking Hijacking* (pengguna tidak dapat menebak ID transaksi pengguna lain untuk melihat detail atau melakukan pembayaran ilegal).
2.  **Proteksi Concurrency & Double Booking (Race Condition):**
    *   Penerapan database transaction (`DB::beginTransaction`) dan penguncian baris data (`lockForUpdate()`) di level database saat proses pemilihan kursi dalam method `store()`.
    *   Menjamin nomor kursi yang sedang diproses tidak bisa dipesan secara bersamaan oleh pengguna lain dalam fraksi detik yang sama.
3.  **Optimasi Database (Indexes):**
    *   Penambahan indeks unik (`unique`) pada kode transaksi (`code`).
    *   Pemberian indeks performa pada kolom yang sering dicari seperti `payment_status`, `class_type`, `sequence` (segmen penerbangan), `flight_number`, dan `role` (untuk meminimalkan scan tabel penuh).
4.  **Sistem Antrean Email (Queue Mailable):**
    *   Proses pembuatan PDF Boarding Pass menggunakan DomPDF cukup memakan CPU.
    *   Mengubah `TransactionSuccessMail` untuk mengimplementasikan kontrak `ShouldQueue` dan mengubah pemanggilan menjadi `Mail::queue()` agar respons aplikasi ke pengguna/webhook Midtrans instan dan terhindar dari error *timeout*.
5.  **Perbaikan Relasi Pivot:**
    *   Memperbaiki relasi model `Facilty::classes()` dari `Flight` menjadi `FlightClass` beserta pembenahan urutan pivot keys yang keliru.
6.  **Generator Kode Transaksi Anti-Crash:**
    *   Menggunakan algoritma *auto-retry loop* (maksimal 5 kali percobaan) saat pembuatan kode booking `GRD-` acak untuk menghindari kegagalan penyimpanan akibat tabrakan kode yang sudah terdaftar.
7.  **Dokumentasi Internal Kode Program (PHPDoc & Block Comments):**
    *   Seluruh file logika program kustom (Services, Livewire Components, Eloquent Models, Policies, dan Requests) didokumentasikan lengkap menggunakan block comments (`/* */`) untuk meningkatkan keterbacaan kode bagi pengembang lain.

---

## Dokumentasi API (Scribe)

Sistem ini dilengkapi dengan dokumentasi API interaktif yang digenerate oleh Scribe.

### Cara Menghasilkan Dokumentasi API
Jalankan perintah berikut di terminal Anda untuk memperbarui dependensi Scribe dan menghasilkan file dokumentasi HTML statis:
```bash
composer update knuckleswtf/scribe
php artisan scribe:install
php artisan scribe:generate
```
Dokumentasi dapat diakses secara statis melalui folder `public/docs/index.html` atau URL dinamis `/docs` di server lokal.

---

## Prasyarat Instalasi

*   **PHP** `^8.3` (dengan ekstensi gd, zip, pdo_sqlite, dll.)
*   **Composer**
*   **Node.js** & NPM
*   Database (MySQL, PostgreSQL, atau SQLite)

---

## Cara Instalasi & Menjalankan Project

### 1. Unduh Source Code
Ekstrak arsip proyek ini atau buka direktori proyek melalui terminal Anda.

### 2. Pasang Dependensi PHP
Jalankan perintah berikut di direktori root proyek:
```bash
composer install
```

### 3. Pasang Dependensi Frontend & Compile Assets
Jalankan perintah berikut untuk menginstal modul Node.js dan meng-compile CSS/JS:
```bash
npm install
npm run build
```

### 4. Konfigurasi Environment File
Salin file `.env.example` menjadi `.env`:
```bash
copy .env.example .env
```
Buka file `.env` yang baru dibuat dan sesuaikan konfigurasi database Anda:
*   Jika menggunakan **MySQL**:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3307
    DB_DATABASE=garuda
    DB_USERNAME=root
    DB_PASSWORD=
    ```
*   Jika menggunakan **SQLite**:
    ```env
    DB_CONNECTION=sqlite
    # Silakan biarkan DB_DATABASE kosong atau arahkan ke database/database.sqlite
    ```
*   Isi kredensial **Mailtrap** untuk uji coba email boarding pass:
    ```env
    MAIL_HOST=sandbox.smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=isi-username-mailtrap-anda
    MAIL_PASSWORD=isi-password-mailtrap-anda
    ```
*   Isi server key dan client key **Midtrans** (Sandbox) jika ingin menguji pembayaran online:
    ```env
    MIDTRANS_SERVER_KEY=isi-server-key-anda
    MIDTRANS_CLIENT_KEY=isi-client-key-anda
    ```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Jalankan Migrasi & Seeder Database
Gunakan perintah ini untuk membuat tabel database dan mengisinya dengan data awal (maskapai, penerbangan, rute, admin):
```bash
php artisan migrate --seed
```

### 7. Jalankan Queue Worker
Karena pengiriman email notifikasi Boarding Pass dikirim via antrean (asinkron), jalankan worker berikut di jendela terminal terpisah:
```bash
php artisan queue:work
```

### 8. Jalankan Server Aplikasi
```bash
php artisan serve
```
Akses aplikasi melalui peramban web di alamat: **`http://127.0.0.1:8000`**

---

## Pengujian Unit & Fitur (Testing Suite)

Proyek ini dilengkapi dengan unit test lengkap untuk memverifikasi fungsionalitas backend, relasi model, kebijakan otorisasi, dan validasi database.

Untuk menjalankan suite pengujian, gunakan perintah:
```bash
php artisan test
```

---

## Kredensial Pengguna Default

Setelah menjalankan seeder database, Anda dapat login dengan akun bawaan berikut:
*   **Email:** `test@example.com`
*   **Password:** `password`
*   **Role:** Admin (bisa mengakses panel admin di URL `/admin` jika panel Filament terkonfigurasi).
