<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

/*
 * Komponen Livewire MyBookings
 *
 * Mengelola antarmuka daftar transaksi/pesanan milik pengguna yang sedang masuk,
 * termasuk fitur pencarian real-time berdasarkan kode booking, status pembayaran,
 * nomor penerbangan, kota, atau kode bandara.
 */
class MyBookings extends Component
{
    /* @var string Kata kunci pencarian transaksi */
    public string $search = '';

    /*
     * Ambil daftar transaksi pengguna sesuai filter pencarian
     *
     * Melakukan pencarian transaksi dengan relasi flight, airline, airport, class,
     * dan promo berdasarkan input teks search.
     *
     * @return \Illuminate\Database\Eloquent\Collection Daftar transaksi
     */
    public function getTransactions()
    {
        $query = Auth::user()->transactions()
            ->with(['flight.airline', 'flight.segments.airport', 'class', 'promo'])
            ->orderBy('created_at', 'desc');

        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function($q) use ($term) {
                $q->where('code', 'like', $term)
                  ->orWhere('payment_status', 'like', $term)
                  ->orWhereHas('flight', function($fq) use ($term) {
                      $fq->where('flight_number', 'like', $term);
                  })
                  ->orWhereHas('flight.segments.airport', function($aq) use ($term) {
                      $aq->where('city', 'like', $term)
                        ->orWhere('iata_code', 'like', $term);
                  });
            });
        }

        return $query->get();
    }

    /*
     * Render view Livewire MyBookings
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.my-bookings-view', [
            'transactions' => $this->getTransactions()
        ]);
    }
}
