<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Halaman Registrasi
     * 
     * Menampilkan halaman formulir pendaftaran pengguna baru.
     * 
     * @group Autentikasi
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Proses Registrasi
     * 
     * Mendaftarkan pengguna baru ke dalam sistem.
     * 
     * @group Autentikasi
     * @bodyParam name string required Nama lengkap pengguna. Example: John Doe
     * @bodyParam email string required Alamat email unik pengguna. Example: john@example.com
     * @bodyParam password string required Password akun minimal 8 karakter. Example: password
     * @bodyParam password_confirmation string required Konfirmasi password. Example: password
     * 
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
