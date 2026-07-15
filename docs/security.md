# Keamanan Sistem

## Autentikasi

- Laravel Breeze dengan scaffolding Blade
- Email verification wajib sebelum akses fitur booking
- Password di-hash menggunakan Bcrypt via `Hash::make()`
- Session-based authentication dengan CSRF protection

## Otorisasi

### TransactionPolicy
Setiap akses ke detail transaksi melalui `Gate::authorize('view', $transaction)`:

```php
public function view(User $user, Transaction $transaction): bool
{
    if ($user->isAdmin()) return true;
    if ($transaction->user_id && $user->id === $transaction->user_id) return true;
    return $user->email === $transaction->email;
}
```

Prioritas pengecekan:
1. Admin selalu diizinkan
2. Owner berdasarkan `user_id` (lebih akurat)
3. Fallback ke email (untuk transaksi lama sebelum migration user_id)

### Admin Panel
- Filament panel hanya bisa diakses oleh admin
- Middleware `auth` + `verified` di semua route booking

## Keamanan Pembayaran

### Midtrans Signature Verification
```php
$computed = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
if ($computed !== $signatureKey) {
    throw new \Exception('Invalid signature');
}
```

Mencegah spoofing callback dari pihak ketiga.

### Exception Handling
```php
try {
    // ...
} catch (\Exception $e) {
    \Log::error('Payment processing failed: ' . $e->getMessage());
    return response()->json(['error' => 'Terjadi kesalahan, silakan coba lagi.'], 500);
}
```

Pesan error umum ditampilkan ke user, detail log hanya untuk debugging.

## Validasi Input

- Server-side validation di setiap method controller
- Laravel `@error` directive untuk feedback user
- Input stripping via `strip_tags` untuk XSS prevention
- CSRF token di setiap form POST
- SQL injection dicegah oleh Eloquent ORM (parameter binding)

## Database Security

- Semua password di-hash
- Soft deletes untuk mencegah kehilangan data
- Foreign key constraints untuk referential integrity
- Mass assignment protection via `$fillable` di setiap model

## Session & Cookie

- Encrypt cookie via `EncryptCookies` middleware
- Authenticated session via `AuthenticateSession` middleware
- Session timeout setelah browser ditutup

## HTTPS & Data Transit

- Midtrans API menggunakan HTTPS
- Webhook endpoint menerima POST dari Midtrans dengan validasi signature
- Tidak ada data sensitif (card number, CVV) yang melewati server -- langsung ke Midtrans Snap

## Best Practices

| Aspek | Implementasi |
|-------|--------------|
| XSS | Blade auto-escape `{{ }}`, strip_tags pada input tertentu |
| CSRF | `@csrf` di semua form POST |
| SQL Injection | Eloquent ORM (parameter binding) |
| Mass Assignment | `$fillable` whitelist |
| Auth | Breeze scaffolding, email verification |
| Payment | Midtrans Snap (PCI DSS compliant) |
| Error Handling | Logging tanpa expose detail ke user |
| Rate Limiting | Belum diimplementasi (todo) |
| CORS | Belum diimplementasi (todo, hanya first-party) |

## Todo Keamanan

- [ ] Implement rate limiting pada endpoint `/booking/store`
- [ ] Implement CORS jika ada frontend terpisah
- [ ] Tambah failed login attempt throttling (built-in Laravel)
- [ ] Audit log untuk perubahan status pembayaran
- [ ] Sanitasi input file upload di admin panel
