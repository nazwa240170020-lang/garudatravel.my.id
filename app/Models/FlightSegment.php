<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model FlightSegment
 *
 * Mengelola data urutan segmen/transit dalam rute penerbangan, termasuk
 * urutan (sequence), waktu keberangkatan/transit (time), serta bandara asal/tujuan.
 */
class FlightSegment extends Model
{
    use HasFactory, SoftDeletes;

    /* @var array Kolom yang dapat diisi secara massal */
    protected $fillable = [
        'sequence',
        'flight_id',
        'airport_id',
        'time',
    ];

    /*
     * Konversi tipe data atribut model
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'time' => 'datetime',
            'sequence' => 'integer',
        ];
    }

    /*
     * Relasi ke penerbangan utama
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
     * Relasi ke bandara pada segmen rute ini
     *
     * Relasi Many-to-One ke model Airport.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function airport()
    {
        return $this->belongsTo(Airport::class);
    }
}
