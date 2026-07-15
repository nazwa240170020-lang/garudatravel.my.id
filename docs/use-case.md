# Use Case Diagram

## Aktor

| Aktor | Deskripsi |
|-------|-----------|
| **User (Tamu)** | Pengunjung yang belum login, hanya bisa melihat halaman utama dan pencarian penerbangan |
| **User (Terotentikasi)** | Pengguna yang sudah login, bisa melakukan pemesanan dan melihat riwayat |
| **Admin** | Pengguna dengan akses ke Filament panel untuk CRUD data master |
| **Sistem** | Backend Laravel yang menangani logika bisnis |
| **Midtrans** | Payment gateway eksternal |

## Daftar Use Case

### UC-01: Melihat Halaman Utama
- **Aktor:** User (Tamu)
- **Deskripsi:** User membuka halaman welcome/dashboard
- **Trigger:** Mengakses route `/`

### UC-02: Registrasi Akun
- **Aktor:** User (Tamu)
- **Deskripsi:** User mendaftar akun baru dengan nama, email, password
- **Prekondisi:** Email belum terdaftar
- **Postkondisi:** Akun baru terbuat, user terotentikasi

### UC-03: Login
- **Aktor:** User (Tamu)
- **Deskripsi:** User login dengan email dan password
- **Postkondisi:** User terotentikasi

### UC-04: Mencari Penerbangan
- **Aktor:** User (Semua)
- **Deskripsi:** User mencari penerbangan berdasarkan rute, tanggal, jumlah penumpang
- **Flow:** Pilih departure, arrival, date, passengers, klik Cari
- **Alternatif:** Filter berdasarkan maskapai, fasilitas, tipe transit

### UC-05: Memilih Kelas Penerbangan
- **Aktor:** User (Terotentikasi)
- **Deskripsi:** User memilih kelas Economy atau Business pada penerbangan yang dipilih
- **Flow:** Kelas ditampilkan inline di kartu penerbangan

### UC-06: Memilih Kursi
- **Aktor:** User (Terotentikasi)
- **Deskripsi:** User memilih kursi untuk setiap penumpang
- **Aturan:** Jumlah kursi terpilih wajib = jumlah penumpang
- **Real-time:** Ketersediaan kursi di-polling tiap 5 detik

### UC-07: Mengisi Data Pemesanan
- **Aktor:** User (Terotentikasi)
- **Deskripsi:** User mengisi data pemesan dan data penumpang
- **Flow:**
  - Data pemesan (name, email, phone) terisi otomatis dari profil
  - Data penumpang (name, DOB, nationality) untuk setiap penumpang
  - Opsional: masukkan kode promo
  - Submit form

### UC-08: Menggunakan Kode Promo
- **Aktor:** User (Terotentikasi)
- **Deskripsi:** User memasukkan kode promo untuk mendapatkan diskon
- **Flow:**
  - Livewire component memvalidasi kode promo via PromoService
  - Diskon dihitung (persentase atau nominal)
  - Grandtotal diperbarui real-time
- **Aturan:** Kode promo harus valid, belum digunakan, belum kadaluarsa

### UC-09: Melakukan Pembayaran
- **Aktor:** User (Terotentikasi), Midtrans
- **Deskripsi:** User membayar booking via Midtrans
- **Flow:**
  - Sistem generate Midtrans Snap token
  - User memilih metode bayar
  - Midtrans memproses pembayaran
  - Sistem polling 5 detik untuk deteksi status
  - Webhook Midtrans mengupdate status

### UC-10: Melihat Riwayat Pemesanan
- **Aktor:** User (Terotentikasi)
- **Deskripsi:** User melihat daftar semua booking miliknya
- **Route:** `/my-bookings`

### UC-11: Melihat Detail Booking
- **Aktor:** User (Terotentikasi)
- **Deskripsi:** User melihat detail lengkap booking tertentu
- **Authorization:** Hanya owner atau admin yang bisa akses

### UC-12: Cek Booking via Kode
- **Aktor:** User (Terotentikasi)
- **Deskripsi:** User mengecek status booking via kode booking + email

### UC-13: Mengelola Master Data (Admin)
- **Aktor:** Admin
- **Deskripsi:** CRUD data master via Filament panel
- **Entitas:** Airlines, Airports, Flights, FlightClasses, FlightSeats, PromoCodes, Transactions
- **Route:** `/admin`

### UC-14: Melihat Statistik (Admin)
- **Aktor:** Admin
- **Deskripsi:** Admin melihat overview statistik transaksi via TransactionStatsWidget
- **Caching:** Data di-cache 60 detik

### UC-15: Menerima Webhook Pembayaran
- **Aktor:** Midtrans, Sistem
- **Deskripsi:** Midtrans mengirim notifikasi pembayaran ke `/midtrans/webhook`
- **Validasi:** Signature hash diverifikasi untuk mencegah spoofing
- **Postkondisi:** Status payment diperbarui, email konfirmasi dikirim

## Skenario Validasi

### Gagal: Promo Tidak Valid
1. User memasukkan kode promo salah
2. PromoService mengembalikan error
3. Tampilkan pesan error, grandtotal tidak berubah

### Gagal: Kursi Tidak Tersedia
1. User memilih kursi yang kebetulan sudah di-booking user lain
2. Polling 5 detik mendeteksi status berubah
3. Kursi otomatis di-disable, jika terpilih akan di-unselect

### Gagal: Pembayaran Gagal / Expired
1. User tidak menyelesaikan pembayaran dalam batas waktu
2. Status tetap `pending`
3. User bisa kembali ke halaman payment untuk coba lagi
