<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration Class: AddIndexesToTables
 * 
 * FILE BARU: Menambahkan indeks unik (unique index) dan indeks pencarian (performance index) 
 * pada kolom-kolom tabel database yang sering digunakan dalam klausa WHERE, JOIN, atau ORDER BY.
 * Berguna untuk meningkatkan performa query aplikasi secara signifikan saat data bertambah besar.
 */
return new class extends Migration
{
    /**
     * Menambahkan indeks ke database.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! $this->indexExists('transactions', 'transactions_code_unique')) {
                $table->unique('code'); // Indeks unik untuk mempercepat pencarian tiket berdasarkan kode booking
            }
            if (! $this->indexExists('transactions', 'transactions_payment_status_index')) {
                $table->index('payment_status'); // Indeks status pembayaran (pending/paid/failed)
            }
        });

        Schema::table('flight_seats', function (Blueprint $table) {
            if (! $this->indexExists('flight_seats', 'flight_seats_class_type_index')) {
                $table->index('class_type'); // Indeks tipe kelas (ekonomi/bisnis) saat memilih kursi
            }
        });

        Schema::table('flight_segments', function (Blueprint $table) {
            if (! $this->indexExists('flight_segments', 'flight_segments_sequence_index')) {
                $table->index('sequence'); // Indeks urutan segmen penerbangan transit
            }
        });

        Schema::table('flights', function (Blueprint $table) {
            if (! $this->indexExists('flights', 'flights_flight_number_index')) {
                $table->index('flight_number'); // Indeks nomor penerbangan untuk pencarian & routing
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! $this->indexExists('users', 'users_role_index')) {
                $table->index('role'); // Indeks peran pengguna (customer/admin) untuk otorisasi panel admin
            }
        });
    }

    /**
     * Membatalkan migrasi (menghapus indeks).
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if ($this->indexExists('users', 'users_role_index')) {
                $table->dropIndex(['role']);
            }
        });

        Schema::table('flights', function (Blueprint $table) {
            if ($this->indexExists('flights', 'flights_flight_number_index')) {
                $table->dropIndex(['flight_number']);
            }
        });

        Schema::table('flight_segments', function (Blueprint $table) {
            if ($this->indexExists('flight_segments', 'flight_segments_sequence_index')) {
                $table->dropIndex(['sequence']);
            }
        });

        Schema::table('flight_seats', function (Blueprint $table) {
            if ($this->indexExists('flight_seats', 'flight_seats_class_type_index')) {
                $table->dropIndex(['class_type']);
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if ($this->indexExists('transactions', 'transactions_code_unique')) {
                $table->dropUnique(['code']);
            }
            if ($this->indexExists('transactions', 'transactions_payment_status_index')) {
                $table->dropIndex(['payment_status']);
            }
        });
    }

    /*
     * PERUBAHAN: Cek index menggunakan Schema::getIndexes() agar kompatibel 
     * dengan database SQLite (in-memory) saat testing & MySQL saat produksi.
     */
    private function indexExists(string $tableName, string $indexName): bool
    {
        if (! Schema::hasTable($tableName)) {
            return false;
        }
        return collect(Schema::getIndexes($tableName))->pluck('name')->contains($indexName);
    }
};
