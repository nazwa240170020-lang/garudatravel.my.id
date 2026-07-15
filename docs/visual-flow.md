# Alur Visual Sistem - Garuda Flight Booking System

Dokumen ini menjelaskan arsitektur sistem dan alur kerja (workflow) pemesanan tiket pada Garuda Flight Booking System secara visual menggunakan diagram kotak ASCII terstruktur yang tertanam langsung di dalam dokumen Markdown.

---

## 1. Arsitektur Layer Sistem (System Layer Architecture)

Diagram di bawah ini menggambarkan pembagian layer aplikasi dari tingkat antarmuka pengguna hingga ke penyimpanan data dan layanan eksternal.

```text
+----------------------------------------------------------------------+
|                         Frontend Client Layer                        |
+----------------------------------------------------------------------+
| - User Interface: Blade Templates, Tailwind CSS, Alpine.js           |
| - Interactive Components: Livewire v4 (SeatMap, ApplyPromo)          |
| - Assets Builder: Vite 8.0                                           |
+----------------------------------------------------------------------+
                                   |
                                   | (Livewire HTTP / AJAX Polling)
                                   v
+----------------------------------------------------------------------+
|                 Backend & Admin Platform (Laravel 13)                |
+----------------------------------------------------------------------+
| - Controllers: DashboardController, BookingController                |
| - Admin Dashboard: Filament Admin Panel v3                           |
| - Logic Services: PromoService, TaxService                           |
| - Authorization: TransactionPolicy                                   |
+----------------------------------------------------------------------+
                                   |
                                   | (Eloquent ORM & Integrasi API)
                                   v
+----------------------------------------------------------------------+
|                     Data & External Service Layer                    |
+----------------------------------------------------------------------+
| - Database Utama: MySQL / MariaDB / SQLite                           |
| - Payment Gateway: Midtrans Snap API & Webhook (POST Callback)       |
| - Queue Worker: Laravel Database Queue (Kirim Email E-Tiket)         |
| - Mail Server: SMTP / Mail Delivery (Notifikasi Tiket PDF)           |
+----------------------------------------------------------------------+
```

---

## 2. Alur Proses Pemesanan Tiket (Core Booking Flow)

Diagram di bawah ini merinci langkah-langkah yang dilalui pelanggan dari pencarian tiket hingga tiket elektronik (E-Ticket) diterbitkan.

```text
+----------------------------------------------------------------------+
|                   1. Cari Penerbangan & Pilih Kelas                  |
+----------------------------------------------------------------------+
| Pelanggan memilih Bandara Asal, Tujuan, Tanggal, dan Jumlah          |
| Penumpang di halaman pencarian. Memilih kelas (Economy/Business)     |
| pada penerbangan yang cocok.                                         |
+----------------------------------------------------------------------+
                                   |
                                   v
+----------------------------------------------------------------------+
|                     2. Pilih Kursi (Livewire Grid)                   |
+----------------------------------------------------------------------+
| Sistem menampilkan peta kursi pesawat secara real-time. Pelanggan    |
| memilih nomor kursi kosong sesuai jumlah penumpang.                  |
+----------------------------------------------------------------------+
                                   |
                                   v
+----------------------------------------------------------------------+
|                  3. Isi Data Penumpang & Kode Promo                  |
+----------------------------------------------------------------------+
| Mengisi nama, tanggal lahir, dan kewarganegaraan penumpang.          |
| Opsional: Memasukkan kode promo untuk pemotongan harga (Livewire).    |
+----------------------------------------------------------------------+
                                   |
                                   v
+----------------------------------------------------------------------+
|               4. Generate Transaksi & Midtrans Snap Token            |
+----------------------------------------------------------------------+
| Sistem membuat record transaksi "pending", mengunci kursi yang       |
| dipilih, dan meminta snap_token dari Midtrans API.                   |
+----------------------------------------------------------------------+
                                   |
                                   v
+----------------------------------------------------------------------+
|                     5. Pembayaran & Webhook Callback                 |
+----------------------------------------------------------------------+
| Pelanggan membayar via Snap. Midtrans mengirim webhook callback.     |
| Sistem memperbarui status transaksi menjadi "paid".                  |
+----------------------------------------------------------------------+
                                   |
                                   v
+----------------------------------------------------------------------+
|                 6. Queue Job: Kirim E-Ticket & Selesai               |
+----------------------------------------------------------------------+
| Sistem menjalankan background job untuk membuat PDF E-Ticket         |
| dan mengirimkannya ke email pelanggan. Halaman reload ke detail tiket. |
+----------------------------------------------------------------------+
```

---

## 3. Alur Otentikasi dan Hak Akses (Authentication & Access Control)

Diagram berikut menjelaskan bagaimana proses pendaftaran pengguna, login, dan pemisahan hak akses antara Pelanggan (Customer) dan Admin.

```text
+----------------------------------------------------------------------+
|                            Pendaftaran Akun                          |
+----------------------------------------------------------------------+
| Pengunjung mengisi form registrasi -> Data disimpan ke database      |
| -> Sistem mengirimkan email verifikasi akun.                         |
+----------------------------------------------------------------------+
                                   |
                                   v
+----------------------------------------------------------------------+
|                             Proses Login                             |
+----------------------------------------------------------------------+
| Pengguna memasukkan email dan password -> Sistem melakukan           |
| pencocokan hash password.                                            |
+----------------------------------------------------------------------+
                                   |
                                   +-----------------+
                                   | (Pengecekan Role)|
                                   v                 v
+--------------------------------------+ +-----------------------------+
|          Peran: Customer             | |         Peran: Admin        |
+--------------------------------------+ +-----------------------------+
| - Redirect ke /dashboard             | | - Redirect ke /admin        |
| - Akses pencarian penerbangan        | | - Hak CRUD data master      |
| - Pembelian tiket & pilih kursi      | | - Akses penuh panel Filament|
| - Melihat histori pemesanan saya     | | - Laporan analitik OLAP     |
+--------------------------------------+ +-----------------------------+
```

---

## 4. Alur Integrasi Pembayaran Real-time (Midtrans API Integration)

Proses verifikasi status pembayaran transaksi secara real-time tanpa membutuhkan server WebSocket konvensional.

```text
+----------+          Minta Token          +------------------+
|          |------------------------------>|                  |
|          |                               |   Midtrans API   |
|          |<------------------------------|                  |
|  System  |          Snap Token           +------------------+
|  Laravel |                                         |
|  Backend |          Notifikasi Webhook             |
|          |<----------------------------------------+
|          |      (POST /api/midtrans/callback)
+----------+
     |
     | Update status = 'paid'
     v
+----------+          Polling 5 Detik      +------------------+
| Database |<------------------------------| Customer Browser |
+----------+                               +------------------+
```
