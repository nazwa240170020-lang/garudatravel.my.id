<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Halaman Konfirmasi Password
     * 
     * Menampilkan formulir konfirmasi password sebelum mengakses area sensitif.
     * 
     * @group Autentikasi
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Proses Konfirmasi Password
     * 
     * Memverifikasi apakah password yang dimasukkan sesuai dengan pengguna saat ini.
     * 
     * @group Autentikasi
     * @bodyParam password string required Password saat ini untuk diverifikasi. Example: password
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
