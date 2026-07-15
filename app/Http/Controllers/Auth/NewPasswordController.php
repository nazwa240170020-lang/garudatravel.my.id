<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Halaman Reset Password Baru
     * 
     * Menampilkan halaman formulir untuk mengisi password baru setelah menekan link reset.
     * 
     * @group Autentikasi
     * @queryParam token string required Token reset password unik dari email. Example: abcde12345
     * @queryParam email string required Alamat email pengguna. Example: john@example.com
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Proses Simpan Password Baru
     * 
     * Menyimpan password baru pengguna setelah tervalidasi dengan token reset.
     * 
     * @group Autentikasi
     * @bodyParam token string required Token reset password unik. Example: abcde12345
     * @bodyParam email string required Alamat email pengguna. Example: john@example.com
     * @bodyParam password string required Password baru minimal 8 karakter. Example: newpassword
     * @bodyParam password_confirmation string required Konfirmasi password baru. Example: newpassword
     * 
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
