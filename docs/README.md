# Garuda Flight Booking System

Sistem pemesanan tiket pesawat berbasis web yang dibangun dengan Laravel 13, Filament, Livewire, dan Midtrans. Proyek ini merupakan tugas universitas yang mensimulasikan platform pemesanan penerbangan dengan fitur pencarian jadwal, pemilihan kursi, pembayaran online, dan manajemen booking.

## Fitur Utama

- Pencarian dan filter penerbangan (rute, tanggal, maskapai, fasilitas)
- Pemilihan kelas (Economy / Business) dan kursi
- Form data penumpang (hingga beberapa penumpang)
- Kode promo dan diskon
- Pembayaran via Midtrans (GoPay, Bank Transfer, dll.)
- Polling status pembayaran 5 detik (real-time tanpa WebSocket)
- My Bookings (daftar pemesanan user)
- Admin panel via Filament untuk CRUD data master

## Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Framework | Laravel 13 |
| Database | MySQL (MariaDB) |
| ORM | Eloquent ORM |
| Admin Panel | Filament 3.x |
| Frontend | Blade + Alpine.js + Tailwind CSS |
| Real-time | Polling (setInterval 30s / 5s) |
| Payment Gateway | Midtrans Snap API |
| Testing | PHPUnit + RefreshDatabase |

## Alur Pemesanan (5 Langkah)

1. Cari penerbangan (rute, tanggal, jumlah penumpang)
2. Pilih kelas dan kursi (inline di hasil pencarian)
3. Isi data pemesan dan data penumpang
4. Konfirmasi dan dapatkan link pembayaran Midtrans
5. Bayar dan tunggu konfirmasi otomatis

## Struktur Direktori

```
app/
  Filament/         -- Resource admin panel
  Http/
    Controllers/    -- BookingController, DashboardController
  Livewire/         -- ApplyPromo component
  Models/           -- Eloquent models
  Policies/         -- TransactionPolicy
  Services/         -- PromoService, TaxService
database/
  migrations/       -- Schema database
  seeders/          -- Data dummy + OLAP refresh
docs/               -- Dokumentasi proyek
resources/views/    -- Blade templates
routes/
  web.php           -- Route frontend
```

## Cara Menjalankan

```bash
composer install
cp .env.example .env   # sesuaikan DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

Buka `http://localhost:8000` untuk akses user, `http://localhost:8000/admin` untuk admin panel.

## Akun Default Seeder

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password |
| User | user@example.com | password |

## Lisensi

Proyek ini dibuat untuk keperluan akademis.
