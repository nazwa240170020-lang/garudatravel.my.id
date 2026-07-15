<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
class AuthenticatedSessionController extends Controller
{
    /**
     * Halaman Login
     * 
     * Menampilkan halaman formulir login pengguna.
     * 
     * @group Autentikasi
     */
    public function create(): View
    {
        return view('auth.login');
    }
    /**
     * Proses Login
     * 
     * Melakukan autentikasi kredensial pengguna dan memulai sesi baru.
     * 
     * @group Autentikasi
     * @bodyParam email string required Alamat email pengguna. Example: john@example.com
     * @bodyParam password string required Password pengguna. Example: password
     * @bodyParam remember boolean Menandai agar sesi tetap aktif setelah browser ditutup. Example: true
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Admin langsung diarahkan ke Filament panel (/admin),
        // user biasa tetap ke /dashboard seperti semula.
        if (Auth::user()->role === 'admin') {
            return redirect()->intended('/admin');
        }

        return redirect()->intended(route('dashboard'));
    }
    /**
     * Proses Logout
     * 
     * Mengeluarkan pengguna dari sistem dan mengakhiri sesi aktif.
     * 
     * @group Autentikasi
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}