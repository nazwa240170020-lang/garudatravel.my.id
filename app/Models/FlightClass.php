<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model FlightClass
 *
 * Mengelola data kategori kelas kabin (Economy, Business) dalam penerbangan tertentu,
 * termasuk harga tiket dasar, jumlah total kapasitas kursi, dan relasi ke fasilitas pendukung.
 */
class FlightClass extends Model
{
    use HasFactory, SoftDeletes;

    /* @var array Kolom yang dapat diisi secara massal */
    protected $fillable = [
        'flight_id',
        'class_type',
        'price',
        'total_seats',
    ];

    /*
     * Relasi ke penerbangan induk
     *
     * Relasi Many-to-One ke model Flight.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    /*
     * Relasi ke seluruh fasilitas kelas penerbangan ini
     *
     * Relasi Many-to-Many ke model Facilty melalui tabel pivot 'flight_class_facilty'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function facilties()
    {
        return $this->belongsToMany(
            Facilty::class,
            'flight_class_facilty',
            'flight_class_id',
            'facilty_id'
        );
    }

    /*
     * Relasi ke seluruh transaksi pemesanan kelas ini
     *
     * Relasi One-to-Many ke model Transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}