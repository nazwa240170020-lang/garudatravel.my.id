# Gambaran Sistem

## Arsitektur Aplikasi

Garuda Flight Booking System menggunakan arsitektur monolithic berbasis Laravel dengan pola MVC (Model-View-Controller). Seluruh komponen backend dan frontend berada dalam satu codebase yang sama.

```
[Browser] <--> [Laravel Route] <--> [Controller] <--> [Service Layer]
                                         |
                                    [Eloquent Models] <--> [MySQL Database]
                                         |
                                    [Blade Views + Alpine.js]
```

## Layer Architecture

### 1. Routing Layer (`routes/web.php`)
Mendefinisikan semua endpoint URL. Mengelompokkan route publik, route terotentikasi, dan route admin (Filament).

### 2. Controller Layer (`app/Http/Controllers/`)
Menangani request HTTP, validasi input, orchestration, dan response.

- **BookingController**: Menangani seluruh flow booking (create, store, payment, webhook, finish, detail, myBookings, AJAX endpoints)
- **DashboardController**: Halaman landing, dashboard user, pencarian penerbangan, choose-tier

### 3. Service Layer (`app/Services/`)
Logika bisnis yang reusable dipisahkan dari controller agar DRY dan mudah di-test.

- **PromoService**: Validasi kode promo, hitung diskon, mark as used, apply discount
- **TaxService**: Hitung pajak (11%), kalkulasi grandtotal

### 4. Model Layer (`app/Models/`)
Eloquent ORM models yang merepresentasikan tabel database. Berisi fillable attributes, casts, relationships, dan scopes.

- **User** -- users table, relasi ke transactions
- **Flight** -- flights table, relasi ke segments, classes
- **FlightClass** -- flight_classes table, harga per kelas
- **FlightSeat** -- flight_seats table, status ketersediaan kursi
- **FlightSegment** -- flight_segments table, rute per segmen
- **Transaction** -- transactions table, data pemesanan
- **TransactionPassenger** -- transaction_passengers table, data penumpang
- **PromoCode** -- promo_codes table, kode diskon
- **Airline** / **Airport** / **Facilty** -- data master

### 5. View Layer (`resources/views/`)
Blade templates dengan Tailwind CSS dan Alpine.js untuk interaktivitas frontend.

- Layout utama ada di `layouts/app.blade.php` dan `layouts/navigation.blade.php`
- Setiap halaman booking menggunakan app layout (kecuali halaman standalone tertentu)
- Real-time polling menggunakan JavaScript `setInterval`

### 6. Policy Layer (`app/Policies/`)
Authorization logic:

- **TransactionPolicy**: view() dan update() -- cek user_id dulu, fallback ke email

## Alur Data Pembayaran

```
User submit form booking
  --> BookingController::store()
    --> Buat Transaction (pending)
    --> PromoService::apply() jika ada kode promo
    --> TaxService::grandTotal() hitung total
    --> Midtrans Snap API request
    --> Redirect ke halaman payment (snap_token)
  --> User bayar via Midtrans
  --> Midtrans callback ke webhook()
    --> Update payment_status = 'paid'
    --> Kirim email konfirmasi
    --> Mark seats sebagai booked
    --> Mark promo sebagai used
  --> Polling 5 detik dari halaman payment
    --> AJAX: ajaxPaymentStatus()
    --> Auto-redirect ke halaman sukses
```

## Database OLTP + OLAP

Sistem menggunakan database yang sama untuk OLTP (transaksional) dan OLAP (analitik). View `olap_daily_summary` menyediakan agregasi harian untuk laporan. Command Artisan `olap:refresh` me-refresh data OLAP secara manual atau via seeder.
