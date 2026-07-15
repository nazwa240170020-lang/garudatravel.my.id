<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model PromoCode
 *
 * Mengelola data kode kupon promosi/diskon yang dapat digunakan oleh pelanggan
 * untuk memotong harga grandtotal transaksi pemesanan.
 */
class PromoCode extends Model
{
    use HasFactory, SoftDeletes;

    /* @var array Kolom yang dapat diisi secara massal */
    protected $fillable = [
        'code',
        'discount_type',
        'discount',
        'valid_until',
        'is_active',
        'usage_limit',
        'used_count',
    ];

    /*
     * Konversi tipe data atribut model
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'valid_until' => 'datetime',
            'discount' => 'integer',
            'is_active' => 'boolean',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
        ];
    }

    /*
     * Relasi ke transaksi pemesanan yang menggunakan promo ini
     *
     * Relasi One-to-Many ke model Transaction menggunakan foreign key 'promo_code_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class, 'promo_code_id');
    }

    public function usages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }

    /*
     * Scope query untuk menyaring promo yang masih aktif/tersedia
     *
     * Promo tersedia bila aktif, belum kedaluwarsa, dan belum mencapai limit.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                  ->orWhereColumn('used_count', '<', 'usage_limit');
            });
    }
}
