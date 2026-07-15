# Entity Relationship Diagram (ERD) Visual - Garuda Flight Booking System

Dokumen ini menyajikan satu diagram hubungan entitas (ERD) utuh dan terpadu untuk Garuda Flight Booking System. Diagram menggunakan representasi tabel kotak ASCII terstruktur yang tertanam langsung di dalam kode Markdown untuk memetakan seluruh relasi dan dependensi database secara terperinci.

---

## Diagram ERD Sistem Terpadu (Unified System ERD)

```text
  +-------------------+       +-------------------+       +-------------------+       +-------------------+
  |       users       |       |    promo_codes    |       |     airlines      |       |     airports      |
  +-------------------+       +-------------------+       +-------------------+       +-------------------+
  | id (PK)   : bigint|       | id (PK)   : bigint|       | id (PK)   : bigint|       | id (PK)   : bigint|
  | email(UK) : string|       | code(UK)  : string|       | iata_code : string|       | iata_code : string|
  | name      : string|       | discount  : int   |       | name      : string|       | name, city: string|
  | role      : string|       | is_used   : bool  |       | logo      : string|       | country   : string|
  +-------------------+       +-------------------+       +-------------------+       +-------------------+
            |                           |                           |                           |
            | 1                         | 1                         | 1                         | 1
            |                           |                           |                           |
            v N                         v N                         v N                         v N
  +---------------------------------------------------+       +-------------------+       +-------------------+
  |                   transactions                    |<------|      flights      |------>|  flight_segments  |
  +---------------------------------------------------+ 1   N +-------------------+ 1   N +-------------------+
  | id (PK)                  : bigint                 |       | id (PK)   : bigint|       | id (PK)   : bigint|
  | user_id (FK)             : bigint                 |       | flight_num: string|       | sequence  : int   |
  | code (UK)                : string                 |       | airline_FK: bigint|       | flight_FK : bigint|
  | flight_id (FK)           : bigint ----------------+       +-------------------+       | airport_FK: bigint|
  | flight_class_id (FK)     : bigint -----------------------+      |           |         | time      : datetm|
  | promo_code_id (FK, Null) : bigint                 |      |      |           |         +-------------------+
  | name, email, phone       : string                 |      |      | 1         | 1
  | payment_status           : string                 |      |      |           |
  | subtotal, discount, grand: int                    |      |      v N         v N
  +---------------------------------------------------+      |  +-----------+ +-----------+
            |                                                |  |  flight_  | |  flight_  |
            | 1                                              |  |  classes  | |   seats   |
            | (contains)                                     |  +-----------+ +-----------+
            v N                                              |  | id (PK)   | | id (PK)   |
  +---------------------------------------------------+      |  | flight_FK | | flight_FK |
  |              transaction_passengers               |      |  | class_type| | name (UK) |
  +---------------------------------------------------+      |  | price     | | row, col  |
  | id (PK)                  : bigint                 |      |  | total_seat| | class_type|
  | transaction_id (FK)      : bigint                 |      |  +-----------+ | is_avail  |
  | flight_seat_id (FK)      : bigint <----------------------+        |       +-----------+
  | name                     : string                 | (class_booked)|             |
  | date_of_birth            : date                   |               |             |
  | nationality              : string                 |               |             | 1
  +---------------------------------------------------+               | 1           | (assigned_to)
                                                                      v             v
                                                                +-----------------------+
                                                                | flight_class_facilty  |
                                                                +-----------------------+
                                                                | flight_class_id (FK)  |
                                                                | facilty_id (FK)       |
                                                                +-----------------------+
                                                                            ^
                                                                            | 1 (featured_in)
                                                                            |
                                                                +-----------------------+
                                                                |       facilties       |
                                                                +-----------------------+
                                                                | id (PK)       : bigint|
                                                                | name, image   : string|
                                                                | description   : text  |
                                                                +-----------------------+
```

---

## Kamus Kunci Hubungan Database (Database Relationships Dictionary)

Berikut adalah daftar lengkap mengenai relasi kunci utama (Primary Key) dan kunci tamu (Foreign Key) yang menghubungkan tabel-tabel di atas:

1. **`users` -> `transactions` (1:N):**
   * Menghubungkan akun pelanggan dengan histori transaksi pembelian tiket.
   * `transactions.user_id` merujuk pada `users.id`.

2. **`promo_codes` -> `transactions` (1:N):**
   * Menghubungkan kode promo yang digunakan pelanggan ke transaksi pemesanan terkait.
   * `transactions.promo_code_id` merujuk pada `promo_codes.id` (bersifat opsional/nullable).

3. **`airlines` -> `flights` (1:N):**
   * Mendefinisikan maskapai penerbangan mana yang mengoperasikan jadwal penerbangan tersebut.
   * `flights.airline_id` merujuk pada `airlines.id`.

4. **`airports` -> `flight_segments` (1:N):**
   * Menentukan bandara asal/transit pada segmen rute perjalanan.
   * `flight_segments.airport_id` merujuk pada `airports.id`.

5. **`flights` -> `flight_segments` (1:N):**
   * Satu penerbangan dapat memiliki beberapa segmen rute perjalanan (transit).
   * `flight_segments.flight_id` merujuk pada `flights.id`.

6. **`flights` -> `flight_classes` (1:N):**
   * Menyediakan pilihan kelas kabin (Economy / Business) untuk satu jadwal penerbangan.
   * `flight_classes.flight_id` merujuk pada `flights.id`.

7. **`flights` -> `flight_seats` (1:N):**
   * Mendata ketersediaan kursi pesawat fisik untuk jadwal penerbangan terkait.
   * `flight_seats.flight_id` merujuk pada `flights.id`.

8. **`flights` -> `transactions` (1:N):**
   * Menghubungkan transaksi dengan penerbangan yang telah dipesan.
   * `transactions.flight_id` merujuk pada `flights.id`.

9. **`flight_classes` -> `transactions` (1:N):**
   * Mengunci kategori kelas kabin yang dibeli oleh pelanggan pada transaksi tersebut.
   * `transactions.flight_class_id` merujuk pada `flight_classes.id`.

10. **`transactions` -> `transaction_passengers` (1:N):**
    * Menghubungkan data transaksi dengan daftar nama penumpang yang didaftarkan.
    * `transaction_passengers.transaction_id` merujuk pada `transactions.id`.

11. **`flight_seats` -> `transaction_passengers` (1:1):**
    * Kursi fisik yang dipilih secara unik untuk satu penumpang dalam manifes transaksi penerbangan.
    * `transaction_passengers.flight_seat_id` merujuk pada `flight_seats.id`.

12. **`flight_classes` <-> `facilties` (M:N via `flight_class_facilty`):**
    * Relasi multi-arah antara kategori kelas kabin dan fasilitas yang didapatkan.
    * `flight_class_facilty.flight_class_id` merujuk pada `flight_classes.id`.
    * `flight_class_facilty.facilty_id` merujuk pada `facilties.id`.
