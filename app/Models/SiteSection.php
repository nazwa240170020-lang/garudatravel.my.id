<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
 * Model SiteSection
 *
 * Mengelola isi konten dinamis untuk bagian halaman depan (welcome section)
 * seperti banner promosi, testimoni, atau informasi tambahan.
 */
class SiteSection extends Model
{
    /* @var array Kolom yang dapat diisi secara massal */
    protected $fillable = [
        'slug',
        'title',
        'subtitle',
        'data',
        'is_active',
    ];

    /*
     * Konversi tipe data atribut model
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /*
     * Scope query untuk menyaring bagian halaman yang berstatus aktif
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
