<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model Airport
 *
 * Mengelola data bandara udara, termasuk nama bandara, kode IATA, kota,
 * negara, berkas gambar pendukung, dan relasi ke segmen penerbangan.
 */
class Airport extends Model
{
    use HasFactory, SoftDeletes;

    /* @var array Kolom yang dapat diisi secara massal */
    protected $fillable = [
        'iata_code',
        'name',
        'image',
        'city',
        'country',
    ];

    /*
     * Relasi ke segmen rute penerbangan
     *
     * Relasi One-to-Many ke model FlightSegment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function segments()
    {
        return $this->hasMany(FlightSegment::class);
    }
}