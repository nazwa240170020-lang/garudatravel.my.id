<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * Model Flight
 *
 * Mengelola data jadwal penerbangan pesawat, termasuk nomor penerbangan,
 * relasi maskapai, segmen bandara rute, kelas kabin, kursi, dan transaksi.
 */
class Flight extends Model
{
    use HasFactory, SoftDeletes;

    /* @var array Kolom yang dapat diisi secara massal */
    protected $fillable = [
        'flight_number',
        'airline_id',
    ];

    /*
     * Relasi ke maskapai penerbangan
     *
     * Relasi Many-to-One ke model Airline.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    /*
     * Relasi ke segmen rute bandara (transit)
     *
     * Relasi One-to-Many ke model FlightSegment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function segments()
    {
        return $this->hasMany(FlightSegment::class);
    }

    /*
     * Relasi ke kelas penerbangan (kabintier)
     *
     * Relasi One-to-Many ke model FlightClass.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function classes()
    {
        return $this->hasMany(FlightClass::class);
    }

    /*
     * Relasi ke seluruh kursi pesawat
     *
     * Relasi One-to-Many ke model FlightSeat.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function seats()
    {
        return $this->hasMany(FlightSeat::class);
    }

    /*
     * Relasi ke seluruh transaksi penerbangan ini
     *
     * Relasi One-to-Many ke model Transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /*
     * Hasilkan kode identifikasi unik kursi
     *
     * Format output: [NomorPenerbangan]-[NomorBaris][HurufKolom]. Contoh: GA123-1A
     *
     * @param int $row Nomor baris kursi
     * @param int $column Urutan kolom kursi (1 = A, 2 = B, dst)
     * @return string Kode kursi lengkap
     */
    public function generateSeatCode(int $row, int $column): string
    {
        $columnLetter = chr(64 + $column);

        return strtoupper(
            $this->flight_number . '-' . $row . $columnLetter
        );
    }

    /*
     * Auto generate kursi pesawat
     *
     * Menghasilkan data kursi otomatis secara massal dan menginputkannya ke database.
     * Menghindari bentrokan baris kursi antar kelas kabin dengan menghitung baris awal maksimum.
     *
     * @param int $totalSeats Total jumlah kursi yang ingin dibuat
     * @param int $seatsPerRow Jumlah kursi dalam satu baris (default 6)
     * @param string $classType Tipe kelas kabin ('economy', 'business')
     * @return void
     */
    public function generateSeats(
        int $totalSeats,
        int $seatsPerRow = 6,
        string $classType = 'economy'
    ): void {
        $startRow = $this->seats()->withTrashed()->max('row') ?? 0;
        $rows = ceil($totalSeats / $seatsPerRow);
        $seatCounter = 1;

        $seats = [];

        for ($row = $startRow + 1; $row <= $startRow + $rows; $row++) {
            for ($column = 1; $column <= $seatsPerRow; $column++) {

                if ($seatCounter > $totalSeats) {
                    break 2;
                }

                $seatCode = $this->generateSeatCode($row, $column);

                $seats[] = [
                    'flight_id'    => $this->id,
                    'name'         => $seatCode,
                    'row'          => $row,
                    'column'       => chr(64 + $column),
                    'is_available' => true,
                    'class_type'   => $classType,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];

                $seatCounter++;
            }
        }

        FlightSeat::insert($seats);
    }
}