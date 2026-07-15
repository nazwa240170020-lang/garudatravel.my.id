<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Perbarui Password Pengguna
     * 
     * Mengubah password pengguna yang sedang masuk setelah memverifikasi password saat ini.
     * 
     * @group Autentikasi
     * @bodyParam current_password string required Password pengguna saat ini. Example: password
     * @bodyParam password string required Password baru minimal 8 karakter. Example: newpassword
     * @bodyParam password_confirmation string required Konfirmasi password baru. Example: newpassword
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
