# Testing

## Struktur Test

Seluruh test menggunakan PHPUnit dengan trait `RefreshDatabase` untuk mengisolasi database setiap test.

```
tests/
  Unit/
    ExampleTest.php          -- Test dasar
    BackendFixTest.php       -- Test model, relasi, policy, factory
    PromoServiceTest.php     -- Test PromoService logic
    TaxServiceTest.php       -- Test TaxService logic
  Feature/
    ExampleTest.php          -- Test halaman welcome
    FrontendRouteTest.php    -- Test route dan otorisasi
    Auth/                    -- Test autentikasi (Breeze scaffolding)
    BookingControllerTest.php -- Test flow booking lengkap
    ProfileTest.php          -- Test profile user
```

## Test Coverage

### Unit Tests

| Test | Assertions | Deskripsi |
|------|------------|-----------|
| `test_transaction_fillable_includes_new_columns` | 1 | Memastikan kolom fillable Transaction sudah termasuk user_id, mail_sent_at |
| `test_transaction_can_be_created_with_discount` | 2 | Membuat transaksi dengan diskon |
| `test_transaction_can_store_payment_columns` | 3 | Menyimpan data pembayaran (paid_at, method, channel) |
| `test_facilty_pivot_table_name_is_correct` | 1 | Memastikan nama pivot table Facilty benar |
| `test_flight_generate_seats_creates_correct_number_of_seats` | 1 | Generate kursi dengan jumlah tepat |
| `test_flight_generate_seats_creates_unique_seat_codes` | 1 | Kode kursi unik |
| `test_transaction_passengers_can_be_inserted_in_bulk` | 2 | Bulk insert penumpang |
| `test_transaction_can_load_class_relationship` | 1 | Eager load relasi class |
| `test_facilty_relates_to_flight_class` | 1 | Relasi Facilty ke FlightClass |
| `test_transaction_policy_authorizes_correctly` | 3 | Otorisasi policy: owner diizinkan, stranger ditolak |
| `test_promo_service_validates_code` | 3 | Validasi kode promo (valid, invalid, expired) |
| `test_promo_service_calculates_percentage_discount` | 1 | Diskon persentase |
| `test_promo_service_calculates_fixed_discount` | 1 | Diskon nominal |
| `test_promo_service_discount_does_not_exceed_subtotal` | 1 | Diskon tidak melebihi subtotal |
| `test_tax_service_calculates_correct_rate` | 2 | Pajak 11% dari subtotal |
| `test_tax_service_grand_total` | 1 | Grandtotal = subtotal + tax - discount |

### Feature Tests

| Test | Assertions | Deskripsi |
|------|------------|-----------|
| `test_welcome_page_renders_successfully` | 1 | Halaman welcome 200 OK |
| `test_dashboard_renders_for_authenticated_users` | 1 | Dashboard user 200 OK |
| `test_flights_search_renders_successfully` | 1 | Pencarian penerbangan 200 OK |
| `test_choose_tier_renders_successfully` | 1 | Halaman choose-tier 200 OK |
| `test_choose_seat_renders_successfully` | 1 | Halaman choose-seat 200 OK |
| `test_booking_create_form_renders_successfully` | 1 | Form booking create 200 OK |
| `test_checkout_page_returns_not_found_after_removal` | 1 | Checkout dihapus, return 404 |
| `test_payment_page_is_authorized_for_owner` | 1 | Payment 200 untuk owner |
| `test_payment_page_is_forbidden_for_non_owner` | 1 | Payment 403 untuk stranger |
| `test_booking_detail_page_is_authorized_for_owner` | 1 | Detail 200 untuk owner |
| `test_booking_detail_page_is_forbidden_for_non_owner` | 1 | Detail 403 untuk stranger |
| `test_booking_store_creates_transaction` | 4 | Store transaksi baru dengan validasi |
| `test_booking_store_rejects_invalid_seats` | 1 | Store gagal jika kursi invalid |
| `test_booking_store_rejects_unauthenticated` | 1 | Store gagal jika tidak login |
| `test_ajax_seats_returns_json` | 2 | Endpoint AJAX seats return JSON valid |
| `test_my_bookings_shows_user_transactions` | 2 | My Bookings menampilkan transaksi user |

## Cara Menjalankan Test

```bash
# Semua test
php artisan test

# Pilih test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Pilih file spesifik
php artisan test tests/Unit/BackendFixTest.php
php artisan test tests/Feature/FrontendRouteTest.php

# Dengan coverage report (Xdebug/PCov required)
php artisan test --coverage
```

## Continuous Integration

Test dijalankan di environment lokal dengan:
- Laragon (PHP 8.4, MySQL 8)
- RefreshDatabase tiap test (in-memory SQLite atau MySQL)
- Factory untuk generate data dummy

## Prinsip Testing

1. Setiap test independen (RefreshDatabase)
2. Factory pattern untuk data konsisten
3. Assertions spesifik dan terukur
4. Test skenario positif dan negatif
5. Test otorisasi (owner vs stranger)
6. Test validasi (input valid vs invalid)
7. Emulate Midtrans webhook dengan HTTP test
