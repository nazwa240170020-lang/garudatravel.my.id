<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/*
 * Form Request LoginRequest
 *
 * Mengelola aturan otorisasi, aturan validasi, autentikasi sesi pengguna,
 * dan pembatasan frekuensi login (rate limiting/throttling).
 */
class LoginRequest extends FormRequest
{
    /*
     * Tentukan apakah pengguna diizinkan membuat permintaan ini
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /*
     * Aturan validasi input login
     *
     * Memastikan email dan password diisi serta format email valid.
     *
     * @return array Aturan validasi
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /*
     * Lakukan autentikasi kredensial pengguna
     *
     * Memeriksa pembatasan percobaan login, mencoba login,
     * serta mencatat kegagalan percobaan login atau menghapusnya jika sukses.
     *
     * @return void
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        /* Pastikan jumlah percobaan login belum melebihi batas (rate limiting) */
        $this->ensureIsNotRateLimited();

        /* Mencoba login menggunakan email dan password */
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            /* Catat satu kali kegagalan percobaan login */
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        /* Hapus riwayat kegagalan percobaan jika login berhasil */
        RateLimiter::clear($this->throttleKey());
    }

    /*
     * Validasi status rate limiting login
     *
     * Menolak permintaan jika user sudah salah memasukkan password sebanyak 5 kali.
     *
     * @return void
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        /* Batasi maksimal 5 kali percobaan salah dalam rentang waktu rate limiter */
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        /* Picu event Lockout Laravel */
        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /*
     * Dapatkan key pencatatan rate limiter unik
     *
     * Menggabungkan email berhuruf kecil dan IP address pengguna.
     *
     * @return string Throttle key
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
