<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/*
 * Model User
 *
 * Mewakili data pengguna/pelanggan di dalam sistem, mengimplementasikan
 * kontrak otentikasi Filament admin (`FilamentUser`).
 */
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /* @var array Kolom yang dapat diisi secara massal (mass-assignable) */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
     * Cek apakah user adalah admin
     *
     * @return bool True jika role user adalah admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /*
     * Tentukan hak akses ke Filament Admin Panel
     *
     * @param Panel $panel Objek panel Filament
     * @return bool True jika diizinkan masuk panel
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    /*
     * Relasi ke transaksi pemesanan
     *
     * Relasi One-to-Many ke model Transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function promoCodeUsages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PromoCodeUsage::class);
    }
}
