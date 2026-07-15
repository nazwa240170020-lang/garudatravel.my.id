# Perancangan Database

## Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        timestamp email_verified_at
        string password
        string role
        string remember_token
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    airlines {
        bigint id PK
        string iata_code UK
        string name
        string logo
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    airports {
        bigint id PK
        string iata_code UK
        string name
        string image
        string city
        string country
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    flights {
        bigint id PK
        string flight_number
        bigint airline_id FK
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    flight_segments {
        bigint id PK
        integer sequence
        bigint flight_id FK
        bigint airport_id FK
        datetime time
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    flight_classes {
        bigint id PK
        bigint flight_id FK
        string class_type
        integer price
        integer total_seats
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    facilties {
        bigint id PK
        string name
        string image
        text description
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    flight_class_facilty {
        bigint flight_class_id FK
        bigint facilty_id FK
    }
    flight_seats {
        bigint id PK
        bigint flight_id FK
        string name UK
        integer row
        string column
        string class_type
        boolean is_available
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    promo_codes {
        bigint id PK
        string code UK
        string discount_type
        integer discount
        datetime valid_until
        boolean is_used
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    transactions {
        bigint id PK
        bigint user_id FK
        string code UK
        bigint flight_id FK
        bigint flight_class_id FK
        string name
        string email
        string phone
        integer number_of_passengers
        bigint promo_code_id FK
        string payment_status
        integer subtotal
        integer discount
        integer grandtotal
        datetime paid_at
        string payment_method
        string payment_channel
        datetime mail_sent_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    transaction_passengers {
        bigint id PK
        bigint transaction_id FK
        bigint flight_seat_id FK
        string name
        date date_of_birth
        string nationality
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    airlines ||--o{ flights : "operates"
    airports ||--o{ flight_segments : "at"

    flights ||--o{ flight_segments : "has"
    flights ||--o{ flight_classes : "offers"
    flights ||--o{ flight_seats : "has_seats"

    flight_classes ||--o{ flight_class_facilty : "features"
    facilties ||--o{ flight_class_facilty : "featured_in"

    users ||--o{ transactions : "makes"
    promo_codes ||--o{ transactions : "applies"

    flights ||--o{ transactions : "booked_in"
    flight_classes ||--o{ transactions : "class_booked"
    transactions ||--o{ transaction_passengers : "contains"
    flight_seats ||--o{ transaction_passengers : "assigned_to"
```

## Relasi Antar Tabel

| Model | Relasi | Target | Foreign Key |
|-------|--------|--------|-------------|
| Flight | belongsTo | Airline | airline_id |
| Flight | hasMany | FlightSegment | flight_id |
| Flight | hasMany | FlightClass | flight_id |
| Flight | hasMany | FlightSeat | flight_id |
| FlightSegment | belongsTo | Airport | airport_id |
| FlightClass | belongsToMany | Facilty | flight_class_facilty |
| FlightClass | hasMany | FlightSeat | flight_id + class_type |
| Transaction | belongsTo | User | user_id |
| Transaction | belongsTo | Flight | flight_id |
| Transaction | belongsTo | FlightClass | flight_class_id |
| Transaction | belongsTo | PromoCode | promo_code_id |
| Transaction | hasMany | TransactionPassenger | transaction_id |
| TransactionPassenger | belongsTo | FlightSeat | flight_seat_id |

## Indexing Strategy

| Tabel | Index | Type | Tujuan |
|-------|-------|------|--------|
| transactions | user_id | B-tree | Lookup booking per user |
| transactions | payment_status | B-tree | Filter by status |
| transactions | code | UNIQUE | Pencarian kode booking |
| flight_seats | flight_id + class_type | Composite | Filter kursi per flight |
| flight_segments | flight_id + sequence | Composite | Urutan segmen |
| promo_codes | code | UNIQUE | Validasi kode promo |
| transaction_passengers | transaction_id | B-tree | Relasi penumpang |

## Migration Files

| File | Perubahan |
|------|-----------|
| `create_users_table` | Tabel users + soft deletes |
| `create_airlines_table` | Master maskapai |
| `create_airports_table` | Master bandara |
| `create_flights_table` | Data penerbangan |
| `create_flight_segments_table` | Segmen rute |
| `create_flight_classes_table` | Kelas harga |
| `create_facilties_table` | Fasilitas |
| `create_flight_class_facilty_table` | Pivot fasilitas |
| `create_flight_seats_table` | Kursi per penerbangan |
| `create_transactions_table` | Transaksi pemesanan |
| `create_transaction_passengers_table` | Penumpang per transaksi |
| `create_promo_codes_table` | Kode promo |
| `add_user_id_to_transactions_table` | Menambahkan user_id ke transactions |
| `fix_unsigned_columns` | Fix tipe kolom unsigned |
| `add_soft_deletes_to_users` | Soft deletes untuk users |
| `remove_redundant_indexes` | Membersihkan index redundant |

## OLAP View

```sql
CREATE VIEW olap_daily_summary AS
SELECT
  DATE(transactions.created_at) AS tanggal,
  COUNT(*) AS total_transaksi,
  SUM(grandtotal) AS total_pendapatan,
  AVG(grandtotal) AS rata_rata_transaksi,
  SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) AS transaksi_lunas,
  SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) AS transaksi_pending
FROM transactions
GROUP BY DATE(created_at);
```

## Konvensi Penamaan

- Tabel: plural snake_case (e.g., `flight_seats`)
- Kolom: singular snake_case (e.g., `payment_status`)
- Foreign key: `{tabel}_id` (e.g., `flight_id`)
- Primary key: `id`
- Soft deletes: `deleted_at`
- Timestamps: `created_at`, `updated_at`
