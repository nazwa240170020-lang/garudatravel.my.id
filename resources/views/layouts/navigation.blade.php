<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-surface/95 backdrop-blur border-b border-border/80">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-center h-14">
            <div class="flex items-center gap-8 h-full">
                <a href="{{ route('landing') }}" wire:navigate class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.svg') }}"
                         alt="Garuda Indonesia"
                         class="w-8 h-8 object-contain">
                    <span class="font-display font-bold text-headline-sm text-primary tracking-wide">Garuda</span>
                </a>

                <div class="hidden sm:flex items-center gap-6 h-full">
                    <a href="{{ route('landing') }}" wire:navigate
                       class="h-full flex items-center border-b-2 {{ request()->routeIs('landing') ? 'border-primary text-primary font-semibold' : 'border-transparent text-on-surface hover:text-primary' }} transition-colors text-body-md">
                        Beranda
                    </a>
                    <a href="{{ route('flights') }}" wire:navigate
                       class="h-full flex items-center border-b-2 {{ request()->routeIs('flights*') ? 'border-primary text-primary font-semibold' : 'border-transparent text-on-surface hover:text-primary' }} transition-colors text-body-md">
                        Penerbangan
                    </a>
                    @auth
                        <a href="{{ route('booking.my-bookings') }}" wire:navigate
                           class="h-full flex items-center border-b-2 {{ request()->routeIs('booking.my-bookings*') || request()->routeIs('booking.check*') ? 'border-primary text-primary font-semibold' : 'border-transparent text-on-surface hover:text-primary' }} transition-colors text-body-md">
                            Pemesanan Saya
                        </a>
                    @endauth
                </div>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <span class="text-body-sm text-tertiary hidden sm:inline">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <x-button variant="secondary" size="sm" type="submit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Keluar
                        </x-button>
                    </form>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="text-nav-link text-on-surface hover:text-accent transition-colors">Masuk</a>
                    <x-button variant="accent" href="{{ route('register') }}" wire:navigate>Daftar</x-button>
                @endauth

                <button @click="open = ! open" class="sm:hidden p-2 text-tertiary hover:text-primary">
                    <svg class="w-5 h-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-border">
        <div class="px-4 py-3 space-y-2">
            <a href="{{ route('landing') }}" wire:navigate class="block text-nav-link {{ request()->routeIs('landing') ? 'text-primary font-semibold' : 'text-on-surface hover:text-primary' }}">Beranda</a>
            <a href="{{ route('flights') }}" wire:navigate class="block text-nav-link {{ request()->routeIs('flights*') ? 'text-primary font-semibold' : 'text-on-surface hover:text-primary' }}">Penerbangan</a>
            @auth
                <a href="{{ route('booking.my-bookings') }}" wire:navigate class="block text-nav-link {{ request()->routeIs('booking.my-bookings*') || request()->routeIs('booking.check*') ? 'text-primary font-semibold' : 'text-on-surface hover:text-primary' }}">Pemesanan Saya</a>
                <a href="{{ route('profile.edit') }}" wire:navigate class="block text-nav-link {{ request()->routeIs('profile.edit*') ? 'text-primary font-semibold' : 'text-on-surface hover:text-primary' }}">Profil</a>
            @endauth
        </div>
    </div>
</nav>
