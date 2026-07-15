@component('mail::message')
# Pembayaran Berhasil 

Halo **{{ $transaction->name }}**,

Terima kasih! Pembayaran kamu telah berhasil dikonfirmasi.

**Kode Booking:** {{ $transaction->code }}  
**Total:** Rp {{ number_format($transaction->grandtotal, 0, ',', '.') }}

@component('mail::button', ['url' => $transaction->id ? route('booking.detail', $transaction->id) : '#'])
Lihat Detail Booking
@endcomponent

Selamat terbang! 

Thanks,<br>
{{ config('app.name') }}
@endcomponent