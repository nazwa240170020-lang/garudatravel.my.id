<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Form Profil Pengguna
     * 
     * Menampilkan halaman formulir untuk mengubah informasi profil pengguna yang sedang masuk.
     * 
     * @group Profil
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Perbarui Profil Pengguna
     * 
     * Memperbarui informasi profil pengguna yang sedang masuk (nama dan email).
     * 
     * @group Profil
     * @bodyParam name string required Nama lengkap pengguna. Example: John Doe
     * @bodyParam email string required Alamat email pengguna. Example: john@example.com
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user) {
            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        }

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Hapus Akun Pengguna
     * 
     * Menghapus akun pengguna yang sedang masuk dari sistem secara permanen.
     * 
     * @group Profil
     * @bodyParam password string required Password saat ini untuk konfirmasi penghapusan. Example: password
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        if (!$user) {
            return Redirect::to('/');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
