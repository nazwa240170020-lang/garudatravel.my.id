<?php

namespace App\Livewire;

use App\Services\PromoService;
use Livewire\Component;

/*
 * Komponen Livewire ApplyPromo
 *
 * Mengelola proses penarikan dan validasi kode promo secara reaktif pada
 * halaman pembayaran, menghitung nilai potongan harga, serta men-dispatch
 * event status promo ke komponen booking utama.
 */
class ApplyPromo extends Component
{
    /* @var float Nilai subtotal transaksi sebelum pajak */
    public float $subtotal;

    /* @var float Nilai pajak transaksi */
    public float $tax;

    /* @var string Input teks kode promo dari pengguna */
    public string $promoCodeInput = '';

    /* @var string|null Pesan respon dari validasi promo */
    public ?string $promoMessage = null;

    /* @var string Status promo ('success' atau 'error') */
    public string $promoStatus = '';

    /* @var float Jumlah nominal diskon yang berhasil didapatkan */
    public float $discount = 0;

    /* @var string Kode promo yang berhasil diaplikasikan */
    public string $appliedPromoCode = '';

    /*
     * Inisialisasi properti biaya transaksi
     *
     * @param float $subtotal
     * @param float $tax
     * @return void
     */
    public function mount(float $subtotal, float $tax): void
    {
        $this->subtotal = $subtotal;
        $this->tax = $tax;
    }

    /*
     * Dapatkan nilai Grand Total akhir (Computed Property)
     *
     * @return float Nilai grand total akhir
     */
    public function getGrandTotalProperty(): float
    {
        return ($this->subtotal + $this->tax) - $this->discount;
    }

    /*
     * Kirim input promo untuk divalidasi dan diaplikasikan
     *
     * @return void
     */
    public function applyPromo(): void
    {
        $code = strtoupper(trim($this->promoCodeInput));

        if ($code === '') {
            $this->promoStatus = 'error';
            $this->promoMessage = 'Silakan masukkan kode promo.';

            $this->dispatch(
                'promo-applied',
                code: '',
                discount: 0,
                grandTotal: $this->grandTotal
            );

            return;
        }

        $result = app(PromoService::class)->apply($code, (int) round($this->subtotal), (int) round($this->tax), auth()->user());

        if (! $result['valid']) {
            $this->discount = 0;
            $this->appliedPromoCode = '';

            $this->promoStatus = 'error';
            $this->promoMessage = $result['message'];

            $this->dispatch(
                'promo-applied',
                code: '',
                discount: 0,
                grandTotal: $this->grandTotal
            );

            return;
        }

        $promo = $result['promo'];
        $this->discount = (float) $result['discount'];

        $discountLabel = $promo->discount_type === 'percentage'
            ? $promo->discount . '%'
            : 'Rp ' . number_format($this->discount, 0, ',', '.');

        $this->appliedPromoCode = $promo->code;

        $this->promoStatus = 'success';
        $this->promoMessage =
            "Kode {$promo->code} berhasil digunakan. Diskon {$discountLabel}";

        $this->dispatch(
            'promo-applied',
            code: $promo->code,
            discount: $this->discount,
            grandTotal: $this->grandTotal
        );
    }

    /*
     * Render view Livewire ApplyPromo
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.apply-promo');
    }
}
