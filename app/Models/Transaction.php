<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model Transaction
 *
 * Mengelola data pesanan tiket penerbangan oleh pengguna, termasuk informasi
 * kontak pemesan, rincian biaya (subtotal, diskon, grandtotal), status pembayaran,
 * detail transfer, serta relasi ke pengguna, penerbangan, kelas, promo, dan data penumpang.
 */
class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    /* @var array Kolom yang dapat diisi secara massal */
    protected $fillable = [
        'user_id',
        'code',
        'flight_id',
        'flight_class_id',
        'name',
        'email',
        'phone',
        'number_of_passengers',
        'promo_code_id',
        'payment_status',
        'subtotal',
        'discount',
        'grandtotal',
        'paid_at',
        'payment_method',
        'payment_channel',
        'mail_sent_at',
        'snap_token',
    ];

    /*
     * Konversi tipe data atribut model
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'mail_sent_at' => 'datetime',
            'payment_status' => 'string',
            'discount' => 'integer',
            'subtotal' => 'integer',
            'grandtotal' => 'integer',
            'number_of_passengers' => 'integer',
        ];
    }

    /*
     * Relasi ke pengguna pemesan tiket
     *
     * Relasi Many-to-One ke model User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
     * Relasi ke kelas penerbangan yang dipesan
     *
     * Relasi Many-to-One ke model FlightClass menggunakan foreign key 'flight_class_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function class()
    {
        return $this->belongsTo(FlightClass::class, 'flight_class_id');
    }

    /*
     * Relasi ke kode promo yang digunakan
     *
     * Relasi Many-to-One ke model PromoCode menggunakan foreign key 'promo_code_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function promo()
    {
        return $this->belongsTo(PromoCode::class, 'promo_code_id');
    }

    public function promoCodeUsage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PromoCodeUsage::class);
    }

    /*
     * Relasi ke daftar penumpang dalam transaksi ini
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
