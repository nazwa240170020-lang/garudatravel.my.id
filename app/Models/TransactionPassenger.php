<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model TransactionPassenger
 *
 * Mengelola detail data penumpang yang ikut serta dalam transaksi pemesanan tiket penerbangan,
 * termasuk nama lengkap, tanggal lahir, kewarganegaraan, dan nomor kursi yang diduduki.
 */
class TransactionPassenger extends Model
{
    use HasFactory, SoftDeletes;

    /* @var array Kolom yang dapat diisi secara massal */
    protected $fillable = [
        'transaction_id',
        'flight_seat_id',
        'name',
        'date_of_birth',
        'nationality',
    ];

    /*
     * Konversi tipe data atribut model
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    /*
     * Relasi ke transaksi utama
     *
     * Relasi Many-to-One ke model Transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /*
     * Relasi ke kursi penerbangan yang diduduki penumpang ini
     *
     * Relasi Many-to-One ke model FlightSeat menggunakan foreign key 'flight_seat_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function seat()
    {
        return $this->belongsTo(FlightSeat::class, 'flight_seat_id');
    }
}
