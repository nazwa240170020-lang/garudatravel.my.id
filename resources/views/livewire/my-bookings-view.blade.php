<div>
    <div class="mb-6">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Cari berdasarkan kode booking (contoh: GRD-), nomor penerbangan, atau kota tujuan..."
            class="w-full bg-surface text-on-surface border border-border rounded-xl px-4 py-3 text-body-md outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors font-sans"
        >
    </div>

    @if($transactions->isEmpty())
        <div class="text-center py-12">
            <p class="font-display text-headline-sm text-tertiary mb-2">Booking tidak ditemukan.</p>
            <x-button variant="secondary" href="{{ route('flights') }}" wire:navigate>
                Cari penerbangan sekarang
            </x-button>
        </div>
    @else
        <div class="space-y-4">
            @foreach($transactions as $t)
                @php
                    $route = $t->flight?->segments?->first()?->airport?->city ?? '-';
                    $routeEnd = $t->flight?->segments?->last()?->airport?->city ?? '-';
                    $statusColor = match($t->payment_status) {
                        'paid' => 'chip bg-green-100 text-green-800 border-green-300',
                        'pending' => 'chip bg-yellow-100 text-yellow-800 border-yellow-300',
                        'failed' => 'chip bg-red-100 text-red-800 border-red-300',
                        default => 'chip bg-gray-100 text-gray-800 border-gray-300',
                    };
                @endphp
                <x-card padding="6" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-display text-headline-sm text-primary">{{ $t->flight?->flight_number ?? '-' }}</span>
                            <span class="text-tertiary mx-2">|</span>
                            <span class="text-body-md text-secondary">{{ $route }} &rarr; {{ $routeEnd }}</span>
                        </div>
                        <span class="{{ $statusColor }}">
                            {{ ucfirst($t->payment_status) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-body-md text-secondary">
                        <div>
                            <span>{{ $t->created_at->format('d M Y') }}</span>
                            <span class="mx-2">|</span>
                            <span>{{ $t->number_of_passengers }} penumpang</span>
                            <span class="mx-2">|</span>
                            <span>Rp {{ number_format($t->grandtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex gap-2">
                            @if($t->payment_status === 'pending')
                                <x-button variant="primary" href="{{ route('booking.payment', $t->id) }}" wire:navigate>
                                    Bayar Sekarang
                                </x-button>
                            @endif
                            <x-button variant="secondary" href="{{ route('booking.detail', $t->id) }}" wire:navigate>
                                Lihat Detail
                            </x-button>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
</div>
