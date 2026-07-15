@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    {{-- Welcome Greeting --}}
    <h1 class="font-display text-headline-lg text-primary mb-8">
        Selamat Datang, {{ Auth::user()->name }}
    </h1>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <x-card class="bg-surface">
            <a href="{{ route('flights') }}" wire:navigate class="flex flex-col items-center gap-4 p-4 text-center no-underline">
                <span class="w-14 h-14 rounded-full bg-neutral text-on-surface flex items-center justify-center">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16v-2l-8-5V3.5a1.5 1.5 0 00-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/></svg>
                </span>
                <div>
                    <h3 class="font-display text-headline-md text-primary">Cari Penerbangan</h3>
                    <p class="text-body-md text-tertiary mt-1">Temukan dan pesan tiket penerbangan terbaik untuk perjalananmu.</p>
                </div>
                <x-button variant="primary" href="{{ route('flights') }}" wire:navigate>Cari Sekarang</x-button>
            </a>
        </x-card>

        <x-card class="bg-surface">
            <a href="{{ route('booking.my-bookings') }}" wire:navigate class="flex flex-col items-center gap-4 p-4 text-center no-underline">
                <span class="w-14 h-14 rounded-full bg-neutral text-on-surface flex items-center justify-center">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="6" width="16" height="14" rx="2"/><path d="M9 6V5a2 2 0 012-2h2a2 2 0 012 2v1"/><line x1="12" y1="11" x2="12" y2="15"/><line x1="10" y1="13" x2="14" y2="13"/></svg>
                </span>
                <div>
                    <h3 class="font-display text-headline-md text-primary">Pemesanan Saya</h3>
                    <p class="text-body-md text-tertiary mt-1">Lihat dan kelola semua pemesanan tiket yang telah kamu buat.</p>
                </div>
                <x-button variant="primary" href="{{ route('booking.my-bookings') }}" wire:navigate>Lihat Pesanan</x-button>
            </a>
        </x-card>

        <x-card class="bg-surface">
            <a href="{{ route('profile.edit') }}" wire:navigate class="flex flex-col items-center gap-4 p-4 text-center no-underline">
                <span class="w-14 h-14 rounded-full bg-neutral text-on-surface flex items-center justify-center">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <div>
                    <h3 class="font-display text-headline-md text-primary">Profil</h3>
                    <p class="text-body-md text-tertiary mt-1">Kelola informasi akun dan pengaturan profil pribadimu.</p>
                </div>
                <x-button variant="primary" href="{{ route('profile.edit') }}" wire:navigate>Edit Profil</x-button>
            </a>
        </x-card>
    </div>

    {{-- Flight Search Form --}}
    <form
        method="GET"
        action="{{ route('flights') }}"
        class="bg-surface border border-border/80 rounded-2xl p-6 shadow-xl relative z-20"
    >
        <h2 class="font-sans font-extrabold text-2xl text-primary mb-6">Cari Penerbangan</h2>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
            
            <!-- Keberangkatan Custom Dropdown -->
            <x-searchable-dropdown 
                name="departure_id" 
                label="Keberangkatan" 
                :selected-id="request('departure_id')"
                :selected-label="request('departure_id') ? $airports->find(request('departure_id'))?->city . ' (' . $airports->find(request('departure_id'))?->iata_code . ')' : 'Pilih Kota'"
                :options="$airports"
            />

            <!-- Kedatangan Custom Dropdown -->
            <x-searchable-dropdown 
                name="arrival_id" 
                label="Tujuan" 
                :selected-id="request('arrival_id')"
                :selected-label="request('arrival_id') ? $airports->find(request('arrival_id'))?->city . ' (' . $airports->find(request('arrival_id'))?->iata_code . ')' : 'Pilih Kota'"
                :options="$airports"
            />

            <!-- Tanggal -->
            <x-date-picker 
                name="date" 
                label="Tanggal" 
                :value="request('date')"
            />

            <!-- Penumpang -->
            <div class="border border-border rounded-xl p-4 flex items-center gap-3 bg-surface hover:border-primary/50 transition-colors">
                <span class="w-9 h-9 rounded-full bg-neutral text-primary flex items-center justify-center text-base shrink-0">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-body-sm text-tertiary mb-0.5">Penumpang</div>
                    <input
                        type="number"
                        min="1"
                        name="passengers"
                        value="1"
                        class="w-full border-0 p-0 font-sans font-semibold text-on-surface focus:ring-0 text-body-lg bg-transparent"
                    >
                </div>
            </div>

            <div class="flex items-center">
                <button
                    type="submit"
                    class="w-full bg-accent hover:bg-accent-hover text-surface rounded-full py-4 font-sans font-bold transition flex items-center justify-center gap-2 shadow-sm duration-200"
                >
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Cari
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
