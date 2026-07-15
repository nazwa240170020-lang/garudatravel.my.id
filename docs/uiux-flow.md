# Alur UI/UX

## Flow Pemesanan (5 Langkah)

### Langkah 1: Cari Penerbangan
**Route:** `GET /flights`

User memilih:
- Bandara keberangkatan
- Bandara tujuan
- Tanggal
- Jumlah penumpang

Submit form akan menampilkan hasil penerbangan yang cocok. Filter sidebar tersedia untuk mempersempit hasil (tipe penerbangan, maskapai, fasilitas).

### Langkah 2: Pilih Kelas
**Route:** `GET /flight/{flight}/booking/{flightClass}/choose-seat`

Setiap kartu penerbangan menampilkan kelas (Economy / Business) langsung di hasil pencarian (inline, tanpa halaman terpisah). User memilih kelas dan diarahkan ke pemilihan kursi.

### Langkah 3: Pilih Kursi
**Route:** `GET /flight/{flight}/booking/{flightClass}/choose-seat?passengers=N`

- Visual grid kursi dengan layout pesawat
- Kode warna: putih (tersedia), abu (terbooking), maroon/primary (terpilih)
- User memilih kursi sesuai jumlah penumpang
- Validasi: jumlah kursi harus sama dengan jumlah penumpang
- Polling 5 detik update ketersediaan kursi via Livewire
- Tombol Continue aktif setelah semua kursi terpilih

### Langkah 4: Isi Data Penumpang
**Route:** `GET /booking/create`

Form input:
- Data pemesan (nama, email, telepon) -- terisi otomatis dari user login
- Data penumpang (nama lengkap, tanggal lahir, kewarganegaraan)
- Setiap penumpang memiliki seat masing-masing
- Summary sidebar: rincian harga, kode promo Livewire, grandtotal
- Submit: POST ke `/booking/store`

### Langkah 5: Pembayaran
**Route:** `GET /booking/{transaction}/payment`

- Menampilkan snap token Midtrans
- User memilih metode bayar (GoPay, Bank Transfer, dll.)
- Polling 5 detik via AJAX `GET /ajax/payment-status/{transaction}`
- Redirect otomatis ke halaman detail saat status lunas

## Halaman Pendukung

### My Bookings
**Route:** `GET /my-bookings`

Daftar semua pemesanan user dengan search & filter, link ke detail.

### Detail Booking
**Route:** `GET /booking/{transaction}`

Informasi lengkap booking: jadwal, penumpang, status pembayaran, tombol cetak tiket.

### Booking Check
**Route:** `GET /booking/check`

Form pengecekan booking via kode booking + email.

## Struktur Navigasi

```
[Header Navbar]
  Logo Garuda -- link ke Dashboard
  Penerbangan -- link ke halaman pencarian
  Pemesanan Saya -- link ke my-bookings
  Profil -- link ke profile/edit
  [Nama User] + Logout

[Footer]
  (minimal, hanya informasi hak cipta)
```

## Responsivitas

- Desktop: layout dua kolom (sidebar filter + hasil)
- Tablet: sidebar tetap, hasil full width
- Mobile: form pencarian single column, filter collapsible

## Real-time Polling

| Halaman | Interval | Endpoint | Tujuan |
|---------|----------|----------|--------|
| Choose Seat | 5 detik | `/ajax/seats` (Livewire) | Update ketersediaan kursi |
| Payment | 5 detik | `/ajax/payment-status/{id}` | Deteksi pembayaran lunas |
