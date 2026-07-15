@extends('layouts.app')

@section('content')
@php
    $segments     = $flight->segments->sortBy('sequence')->values();
    $firstSegment = $segments->first();
    $lastSegment  = $segments->last();
    $transitCount = max($segments->count() - 2, 0);

    $durationMinutes = null;
    if ($firstSegment?->time && $lastSegment?->time) {
        $durationMinutes = \Carbon\Carbon::parse($firstSegment->time)
            ->diffInMinutes(\Carbon\Carbon::parse($lastSegment->time));
    }
@endphp

<div class="max-w-6xl mx-auto px-6 py-10">
    <x-button variant="secondary" href="{{ route('flight.choose-tier', ['flight' => $flight->flight_number, 'passengers' => $passengers]) }}" class="mb-6">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
        Kembali ke Pilih Kelas
    </x-button>

    <h1 class="font-display text-headline-lg text-primary mb-6">Pilih Kursi</h1>

    <form method="GET" action="{{ route('booking.create') }}" id="seatForm">
        <input type="hidden" name="flight_id" value="{{ $flight->id }}">
        <input type="hidden" name="flight_class_id" value="{{ $flightClass->id }}">
        <input type="hidden" name="passengers" value="{{ $passengers }}">
        <input type="hidden" name="seats" id="selectedSeatsInput" value="">

        <div class="flex gap-6 items-start flex-wrap lg:flex-nowrap">

            {{-- Flight Info Sidebar --}}
            <x-card class="w-full lg:w-80 shrink-0 space-y-4">
                <div class="flex justify-between gap-4">
                    <div>
                        <p class="text-label-md text-tertiary">Keberangkatan</p>
                        <p class="font-display text-headline-sm text-primary">
                            {{ $firstSegment?->airport?->name }}
                            ({{ $firstSegment?->airport?->iata_code }})
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-label-md text-tertiary">Kedatangan</p>
                    <p class="font-display text-headline-sm text-primary">
                        {{ $lastSegment?->airport?->name }}
                        ({{ $lastSegment?->airport?->iata_code }})
                    </p>
                </div>

                <div class="flex justify-between gap-4">
                    <div>
                        <p class="text-label-md text-tertiary">Tanggal</p>
                        <p class="text-body-md font-semibold text-primary">
                            {{ $firstSegment?->time ? \Carbon\Carbon::parse($firstSegment->time)->format('d F Y') : '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-label-md text-tertiary">Jumlah</p>
                        <p class="text-body-md font-semibold text-primary">{{ $passengers }} orang</p>
                    </div>
                </div>

                <hr class="border-border">

                <div class="flex items-center gap-3">
                    <img src="{{ $flight->airline->logo_url }}"
                        alt="{{ $flight->airline->name }}"
                        class="h-6 object-contain">
                    <div>
                        <p class="font-display text-headline-sm text-primary">{{ $flight->airline?->name }}</p>
                        <p class="text-body-sm text-tertiary">
                            {{ $firstSegment?->time ? \Carbon\Carbon::parse($firstSegment->time)->format('H:i') : '-' }}
                            -
                            {{ $lastSegment?->time ? \Carbon\Carbon::parse($lastSegment->time)->format('H:i') : '-' }}
                        </p>
                    </div>
                </div>

                @if($durationMinutes)
                    <p class="text-body-sm text-tertiary">
                        {{ intdiv($durationMinutes, 60) }} Jam {{ $durationMinutes % 60 }} Menit
                    </p>
                @endif

                <div class="flex justify-between items-center">
                    <p class="text-body-md font-semibold text-primary">
                        {{ $firstSegment?->airport?->iata_code }}
                        <span class="text-tertiary mx-1">&#8594;</span>
                        {{ $lastSegment?->airport?->iata_code }}
                    </p>
                    <p class="font-display text-headline-sm text-secondary">
                        Rp {{ number_format($flightClass->price, 0, ',', '.') }}
                    </p>
                </div>

                <p class="text-body-sm text-tertiary">
                    {{ $transitCount === 0 ? 'Langsung' : 'Transit '.$transitCount.'x' }}
                </p>

                <hr class="border-border">

                <div class="flex items-center gap-3">
                    <div class="h-12 w-16 rounded-sm overflow-hidden shrink-0 border border-border bg-neutral flex items-center justify-center">
                        @if($flightClass->class_type === 'business')
                            @php
                                $businessImg = 'images/Lufthansa-Boeing-787-Dreamliner-Business-Class-Frankfurt-Newark-EWR-Zach-Griff-61.webp';
                            @endphp
                            <img src="{{ file_exists(public_path($businessImg)) ? asset($businessImg) : asset('images/logo.svg') }}"
                                 alt="Kabin Bisnis"
                                 class="w-full h-full object-cover">
                        @else
                            @php
                                $economyImg = 'images/asiana_economy03-1024x576.jpg';
                            @endphp
                            <img src="{{ file_exists(public_path($economyImg)) ? asset($economyImg) : asset('images/logo.svg') }}"
                                 alt="Kabin Ekonomi"
                                 class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div>
                        <x-chip>{{ ucfirst($flightClass->class_type) }} Class</x-chip>
                        <p class="text-body-sm text-tertiary mt-1">
                            Rp {{ number_format($flightClass->price, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-label-md text-tertiary mb-1">Kursi Dipilih</p>
                    <p id="selectedSeatsText" class="font-display text-headline-sm text-primary">
                        -
                    </p>
                </div>

                 <button type="submit" id="continueBtn" disabled
                    class="w-full bg-primary hover:bg-primary-hover text-surface rounded-full px-6 py-3 font-semibold text-body-md transition disabled:opacity-40 disabled:cursor-not-allowed">
                    Lanjutkan
                </button>
            </x-card>

            {{-- Seat Grid --}}
            <div class="flex-1 p-8 relative overflow-hidden">
                <img src="{{ asset('images/plane-outline.svg') }}"
                     alt=""
                     aria-hidden="true"
                     class="absolute inset-0 w-full h-full object-contain opacity-30 pointer-events-none -z-0">

                <div class="relative z-10">
                    <div class="text-center mb-6">
                        <x-chip class="text-headline-sm font-display font-bold px-6 py-2">
                            {{ ucfirst($flightClass->class_type) }} Class
                        </x-chip>
                    </div>

                    {{-- Legend --}}
                    <div class="flex justify-center gap-6 mb-8">
                        <div class="flex items-center gap-2 text-body-sm text-tertiary">
                            <span class="w-4 h-4 rounded-sm bg-surface border border-border inline-block"></span>
                            Tersedia
                        </div>
                        <div class="flex items-center gap-2 text-body-sm text-tertiary">
                            <span class="w-4 h-4 rounded-sm bg-gray-300 inline-block"></span>
                            Dipesan
                        </div>
                        <div class="flex items-center gap-2 text-body-sm text-tertiary">
                            <span class="w-4 h-4 rounded-sm bg-primary inline-block"></span>
                            Dipilih
                        </div>
                    </div>

                    {{-- Livewire 4 Reactive Seat Map --}}
                    <livewire:seat-map 
                        :flight-id="$flight->id" 
                        :flight-class-id="$flightClass->id" 
                        :passengers="$passengers" 
                    />
                </div>
            </div>

        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('seats-updated', event => {
            let val = event.detail.seats;
            document.getElementById('selectedSeatsInput').value = val;
            let displayVal = val ? val.split(',').map(s => s.substring(s.lastIndexOf('-') + 1)).join(', ') : '-';
            document.getElementById('selectedSeatsText').textContent = displayVal;
            document.getElementById('continueBtn').disabled = !val || val.split(',').length !== {{ $passengers }};
        });

        window.addEventListener('alert', event => {
            if (window.showNotification) {
                window.showNotification('error', event.detail.message);
            } else {
                alert(event.detail.message);
            }
        });
    });
</script>
@endsection
