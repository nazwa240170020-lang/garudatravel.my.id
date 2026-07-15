@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto px-6 py-10">
    <x-button variant="secondary" href="{{ route('booking.my-bookings') }}" class="mb-6">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Kembali ke My Bookings
    </x-button>

    <h1 class="font-display text-headline-lg text-primary mb-6">Detail Booking</h1>

    @if(session('success'))
        <div class="flex items-start gap-3 border rounded-md p-4 mb-5 text-body-sm bg-green-100 text-green-700 border-green-400">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div class="flex items-start gap-3 border rounded-md p-4 mb-5 text-body-sm bg-blue-100 text-blue-700 border-blue-400">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('info') }}
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-start gap-3 bg-error/10 border border-error/30 rounded-md p-4 mb-5 text-body-sm text-error">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <x-card padding="6" class="space-y-5">
        <div class="flex justify-between items-center">
            <p class="text-label-md text-tertiary">Kode Booking</p>
            <p class="font-display text-headline-sm text-primary">{{ $transaction->code }}</p>
        </div>

        <hr class="border-border">

        <div>
            <p class="text-label-md text-tertiary mb-2">Info Penerbangan</p>
            <div class="space-y-1">
                <p class="text-body-md"><span class="font-medium text-secondary">Maskapai:</span> {{ $transaction->flight->airline->name }}</p>
                <p class="text-body-md"><span class="font-medium text-secondary">Nomor:</span> {{ $transaction->flight->flight_number }}</p>
                <p class="text-body-md"><span class="font-medium text-secondary">Kelas:</span> {{ $transaction->class->class_type }}</p>
            </div>
        </div>

        <hr class="border-border">

        <div>
            <p class="text-label-md text-tertiary mb-2">Rute Penerbangan</p>
            @foreach($transaction->flight->segments as $seg)
                <p class="text-body-md">
                    {{ $seg->airport->city }} ({{ $seg->airport->iata_code }})
                    @if($seg->time)
                        - {{ $seg->time->format('d M H:i') }}
                    @endif
                </p>
            @endforeach
        </div>

        <hr class="border-border">

        <div>
            <p class="text-label-md text-tertiary mb-2">Data Penumpang</p>
            @foreach($transaction->passengers as $pax)
                <div class="flex justify-between text-body-md py-1">
                    <span>{{ $pax->name }}</span>
                    <span class="text-tertiary">{{ $pax->seat->name ?? '-' }}</span>
                </div>
            @endforeach
        </div>

        <hr class="border-border">

        <div>
            <p class="text-label-md text-tertiary mb-2">Detail Pembayaran</p>
            <div class="flex justify-between text-body-md">
                <span>Subtotal</span>
                <span>Rp {{ number_format($transaction->subtotal ?? 0, 0, ',', '.') }}</span>
            </div>
            @if($transaction->discount > 0)
                <div class="flex justify-between text-body-md text-green-600">
                    <span>Diskon</span>
                    <span>- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between text-body-md font-bold mt-2 pt-2 border-t border-border">
                <span>Total</span>
                <span>Rp {{ number_format($transaction->grandtotal ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="mt-2">
                <span class="chip
                    @if($transaction->payment_status === 'paid') bg-green-100 text-green-800 border-green-300
                    @elseif($transaction->payment_status === 'pending') bg-yellow-100 text-yellow-800 border-yellow-300
                    @else bg-red-100 text-red-800 border-red-300 @endif">
                    {{ ucfirst($transaction->payment_status) }}
                </span>
            </div>
        </div>

        @if($transaction->payment_status === 'pending')
            <div class="space-y-3">
                <button onclick="window.location.href='{{ route('booking.payment', $transaction->id) }}'"
                    class="w-full flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-surface rounded-full px-6 py-4 font-semibold text-body-md transition shadow-md">
                    Bayar Sekarang
                </button>
                <form id="cancelBookingForm" action="{{ route('booking.cancel', $transaction->id) }}" method="POST">
                    @csrf
                    <button type="button"
                        onclick="window.showConfirm('Batalkan Pesanan', 'Apakah Anda yakin ingin membatalkan pesanan ini? Tindakan ini tidak dapat dibatalkan.', () => { document.getElementById('cancelBookingForm').submit() })"
                        class="w-full flex items-center justify-center gap-2 bg-transparent hover:bg-red-50 text-red-600 border border-red-600 rounded-full px-6 py-3 font-semibold text-body-md transition">
                        Batalkan Pesanan
                    </button>
                </form>
            </div>
        @endif
    </x-card>
</div>
@endsection
