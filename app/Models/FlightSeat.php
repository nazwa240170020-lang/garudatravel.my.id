<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model FlightSeat
 *
 * Mengelola data kursi spesifik di pesawat (misal row 1, col A, kelas economy),
 * status ketersediaan kursi, dan relasi ke penerbangan serta penumpang yang mendudukinya.
 */
class FlightSeat extends Model
{
    use HasFactory, SoftDeletes;

    /* @var array Kolom yang dapat diisi secara massal */
    protected $fillable = [
        'flight_id',
        'name',
        'row',
        'column',
        'class_type',
        'is_available',
    ];

    /*
     * Konversi tipe data atribut model
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'row' => 'integer',
        ];
    }

    /*
     * Relasi ke penerbangan terkait
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
     * Relasi ke transaksi penumpang yang memesan kursi ini
     *
     * Relasi One-to-Many ke model TransactionPassenger.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function passengers()
    {
        return $this->hasMany(TransactionPassenger::class);
    }
}
