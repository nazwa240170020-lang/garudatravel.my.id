<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/*
 * Model Airline
 *
 * Mengelola data maskapai penerbangan, termasuk nama maskapai, kode IATA maskapai,
 * logo resmi, dan relasi ke penerbangan yang dioperasikan.
 */
class Airline extends Model
{
    use HasFactory, SoftDeletes;

    /* @var array Kolom yang dapat diisi secara massal */
    protected $fillable = [
        'iata_code',
        'name',
        'logo',
    ];

    /*
     * Relasi ke seluruh penerbangan maskapai
     *
     * Relasi One-to-Many ke model Flight.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function flights()
    {
        return $this->hasMany(Flight::class);
    }

    /*
     * Dapatkan URL berkas logo maskapai
     *
     * Mengembalikan URL absolut logo jika diunggah admin,
     * jika tidak, akan mengembalikan URL fallback ke logo.svg default.
     *
     * @return string URL berkas logo
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return asset('storage/' . $this->logo);
        }

        return asset('images/logo.svg');
    }
}
