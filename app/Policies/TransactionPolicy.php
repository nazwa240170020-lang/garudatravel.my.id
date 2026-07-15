<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

/*
 * Kebijakan TransactionPolicy
 *
 * Mengelola otorisasi akses (permissions) untuk melihat atau memperbarui detail transaksi
 * pemesanan tiket penerbangan.
 */
class TransactionPolicy
{
    /*
     * Validasi hak akses melihat detail transaksi (UC-11)
     *
     * Transaksi dapat dilihat oleh admin, pemilik akun yang melakukan pemesanan,
     * atau tamu dengan email yang cocok pada detail transaksi.
     *
     * @param User $user Pengguna yang sedang masuk
     * @param Transaction $transaction Objek transaksi yang diakses
     * @return bool True jika diizinkan mengakses detail transaksi
     */
    public function view(User $user, Transaction $transaction): bool
    {
        /* Admin selalu diizinkan melihat semua transaksi */
        if ($user->isAdmin()) {
            return true;
        }

        /* Diizinkan jika ID pengguna cocok dengan pemilik transaksi */
        if ($transaction->user_id && $user->id === $transaction->user_id) {
            return true;
        }

        /* Diizinkan jika email pengguna cocok dengan email transaksi */
        return $user->email === $transaction->email;
    }

    /*
     * Validasi hak akses melakukan pembayaran atau pembatalan transaksi (UC-07/UC-08)
     *
     * Transaksi dapat diperbarui oleh admin, pemilik akun yang melakukan pemesanan,
     * atau tamu dengan email yang cocok pada detail transaksi.
     *
     * @param User $user Pengguna yang sedang masuk
     * @param Transaction $transaction Objek transaksi yang diakses
     * @return bool True jika diizinkan mengubah transaksi
     */
    public function update(User $user, Transaction $transaction): bool
    {
        /* Admin selalu diizinkan mengubah semua transaksi */
        if ($user->isAdmin()) {
            return true;
        }

        /* Diizinkan jika ID pengguna cocok dengan pemilik transaksi */
        if ($transaction->user_id && $user->id === $transaction->user_id) {
            return true;
        }

        /* Diizinkan jika email pengguna cocok dengan email transaksi */
        return $user->email === $transaction->email;
    }
}
