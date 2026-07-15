<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model Facilty
 *
 * Mengelola data fasilitas kabin (seperti makan malam, bagasi gratis, WiFi, USB port)
 * yang ditawarkan pada setiap kelas penerbangan.
 */
class Facilty extends Model
{
    use HasFactory, SoftDeletes;

    /* @var array Kolom yang dapat diisi secara massal */
    protected $fillable = [
        'image',
        'name',
        'description',
    ];

    /*
     * Relasi belongsToMany ke model FlightClass (Kelas Penerbangan).
     * 
     * Relasi Many-to-Many ke model FlightClass melalui tabel pivot 'flight_class_facilty'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function classes()
    {
        return $this->belongsToMany(FlightClass::class, 'flight_class_facilty', 'facilty_id', 'flight_class_id');
    }
}
