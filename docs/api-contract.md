# Kontrak API

## Endpoint Publik (tanpa autentikasi)

### GET /flights
Mencari penerbangan berdasarkan parameter.

**Query Parameters:**
| Parameter | Tipe | Wajib | Deskripsi |
|-----------|------|-------|-----------|
| departure_id | integer | Ya | ID bandara keberangkatan |
| arrival_id | integer | Ya | ID bandara tujuan |
| date | string (date) | Ya | Tanggal keberangkatan (Y-m-d) |
| passengers | integer | Tidak | Jumlah penumpang (default 1) |
| transit_type[] | string | Tidak | Filter tipe transit (direct, transit_1x, transit_2x) |
| airline_id[] | integer | Tidak | Filter maskapai |
| facility_id[] | integer | Tidak | Filter fasilitas |

**Response:** Blade view `flights` dengan data flights, airports, airlines, facilities.

### POST /midtrans/webhook
Menerima notifikasi pembayaran dari Midtrans.

**Request Body:**
```json
{
  "transaction_status": "capture|settlement|pending|deny|cancel|expire",
  "order_id": "GRD-XXXX",
  "payment_type": "gopay|bank_transfer",
  "gross_amount": "550000.00",
  "signature_key": "hash_sha512",
  "status_code": "200",
  "transaction_id": "midtrans-trx-id"
}
```

**Response:** `200 OK`

## Endpoint Terotentikasi (middleware: auth, verified)

### GET /flight/{flight:flight_number}/booking/{flightClass}/choose-seat
Menampilkan halaman pemilihan kursi.

**Parameters:**
| Parameter | Tipe | Wajib | Deskripsi |
|-----------|------|-------|-----------|
| flight | string (flight_number) | Ya | Nomor penerbangan |
| flightClass | integer | Ya | ID flight class |
| passengers | integer | Query | Jumlah penumpang |

### GET /booking/create
Form data penumpang.

**Query Parameters:**
| Parameter | Tipe | Wajib | Deskripsi |
|-----------|------|-------|-----------|
| flight_id | integer | Ya | ID penerbangan |
| flight_class_id | integer | Ya | ID kelas penerbangan |
| passengers | integer | Ya | Jumlah penumpang |
| seats | string | Ya | Kursi dipilih (coma separated) |

### POST /booking/store
Menyimpan transaksi baru.

**Request Body:**
| Field | Tipe | Wajib | Deskripsi |
|-------|------|-------|-----------|
| flight_id | integer | Ya | ID penerbangan |
| flight_class_id | integer | Ya | ID kelas |
| number_of_passengers | integer | Ya | Jumlah penumpang |
| name | string | Ya | Nama pemesan |
| email | string | Ya | Email pemesan |
| phone | string | Ya | Telepon pemesan |
| passengers[][seat_id] | integer | Ya | ID kursi |
| passengers[][name] | string | Ya | Nama penumpang |
| passengers[][dob] | date | Ya | Tanggal lahir |
| passengers[][nationality] | string | Ya | Kewarganegaraan |
| promo_code | string | Tidak | Kode promo (opsional) |
| discount | integer | Tidak | Nilai diskon (opsional) |

**Response:** Redirect ke `booking.payment` jika berhasil.

### GET /booking/{transaction}/payment
Halaman pembayaran Midtrans.

**Authorization:** TransactionPolicy::view()

### GET /booking/{transaction}/finish
Callback setelah user selesai bayar di Midtrans.

### GET /booking/{transaction}
Detail booking.

**Authorization:** TransactionPolicy::view()

### POST /booking/{transaction}/cancel
Membatalkan pemesanan yang masih pending.

**Authorization:** TransactionPolicy::update()
**Response:** Redirect ke `booking.my-bookings` dengan flash message sukses.

### GET /my-bookings
Daftar booking user.

### GET /ajax/seats
AJAX endpoint untuk polling ketersediaan kursi.

**Query Parameters:**
| Parameter | Tipe | Wajib |
|-----------|------|-------|
| flight_id | integer | Ya |
| class_type | string | Ya |

**Response:** JSON
```json
{
  "seats": [
    {"id": 1, "name": "12A", "available": true},
    {"id": 2, "name": "12B", "available": false}
  ]
}
```

### GET /ajax/payment-status/{transaction}
AJAX endpoint untuk polling status pembayaran.

**Response:** JSON
```json
{
  "status": "paid|pending|failed",
  "redirect_url": "http://127.0.0.1:8000/booking/1"
}
```

### GET /promo/check
Cek validitas kode promo (opsional endpoint alternatif).

### POST /booking/{transaction}/pay-bank
Menangani pembayaran via bank transfer manual.

## Error Response

Validation errors mengembalikan redirect dengan `$errors` bag.

```json
{
  "error": "Message error dalam Bahasa Indonesia"
}
```

## HTTP Status Codes

| Code | Deskripsi |
|------|-----------|
| 200 | Sukses |
| 302 | Redirect (login required, post-submit) |
| 403 | Forbidden (bukan owner/admin) |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |
