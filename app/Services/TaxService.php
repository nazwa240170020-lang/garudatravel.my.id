<?php

namespace App\Services;

/*
 * Layanan TaxService
 *
 * Mengelola penentuan tarif pajak pertambahan nilai (PPN)
 * dan kalkulasi pajak serta grand total biaya transaksi pemesanan.
 */
class TaxService
{
    /*
     * Ambil tarif pajak aktif
     *
     * Membaca nilai persentase pajak dari file konfigurasi `app.tax_rate`,
     * dengan nilai default 11% (0.11) jika tidak diset.
     *
     * @return float Tarif pajak dalam bentuk desimal
     */
    public function rate(): float
    {
        return (float) config('app.tax_rate', 0.11);
    }

    /*
     * Hitung besar nominal pajak
     *
     * Menghitung nilai pajak dari jumlah nominal tertentu berdasarkan tarif pajak aktif.
     *
     * @param int $amount Nominal biaya yang akan dipajaki
     * @return int Nilai nominal pajak setelah pembulatan
     */
    public function calculate(int $amount): int
    {
        return (int) round($amount * $this->rate());
    }

    /*
     * Hitung total rincian biaya transaksi
     *
     * Menghasilkan struktur array berisi detail biaya: subtotal, nominal pajak,
     * potongan diskon promo, dan grandtotal akhir (tidak boleh kurang dari 0).
     *
     * @param int $subtotal Total harga tiket sebelum pajak
     * @param int $discount Nilai potongan harga diskon promo (default 0)
     * @return array Rincian biaya transaksi lengkap
     */
    public function grandTotal(int $subtotal, int $discount = 0): array
    {
        $tax = $this->calculate($subtotal);
        $grandTotal = $subtotal + $tax - $discount;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'grandtotal' => max(0, $grandTotal),
        ];
    }
}
