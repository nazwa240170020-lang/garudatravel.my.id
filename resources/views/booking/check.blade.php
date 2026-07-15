@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto px-6 py-10">
    <h1 class="font-display text-headline-lg text-primary mb-6">Cek Booking</h1>

    @if(session('error'))
        <div class="flex items-start gap-3 bg-error/10 border border-error/30 rounded-md p-4 mb-5 text-body-sm text-error">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <x-card padding="6" class="space-y-5">
        <p class="text-body-md text-tertiary">
            Masukkan kode booking kamu untuk melihat detail transaksi dan status pembayaran.
        </p>

        <form method="GET" action="{{ route('booking.check') }}" class="space-y-4">
            <div>
                <label for="code" class="block text-label-md text-tertiary mb-1">Kode Booking</label>
                <input type="text" name="code" id="code"
                    value="{{ old('code', request('code')) }}"
                    placeholder="GRD-XXXXXXXX"
                    class="input-field uppercase tracking-wide"
                    autofocus>
            </div>

            <button type="submit"
                class="w-full flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-surface rounded-full px-6 py-4 font-semibold text-body-md transition shadow-md">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                Cek Booking
            </button>
        </form>
    </x-card>
</div>
@endsection
