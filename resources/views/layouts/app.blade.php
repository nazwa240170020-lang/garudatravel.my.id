<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css'])
        @unless(request()->routeIs('booking.choose-seat'))
            @vite(['resources/js/app.js'])
        @endunless
        @livewireStyles
        <style>[x-cloak] { display: none !important; }</style>
    </head>
    <body
        class="bg-cover bg-center bg-fixed bg-no-repeat"
        style="background-image: url('{{ asset('images/WhatsApp Image 2026-06-14 at 16.52.35.webp') }}');">
            @section('navigation')
                @include('layouts.navigation')
            @show

            @isset($header)
                <header class="border-b border-border bg-surface">
                    <div class="max-w-7xl mx-auto py-6 px-6">
                        <h2 class="font-display text-headline-md text-primary">{{ $header }}</h2>
                    </div>
                </header>
            @endisset

            <main>
                @yield('content', $slot ?? '')
            </main>
        </div>

        <!-- Global Toast Notifications Container -->
        <div id="globalToastContainer" class="fixed top-5 right-5 z-[100] flex flex-col gap-3 w-full max-w-sm pointer-events-none"></div>

        <!-- Global Confirmation Modal Container -->
        <div 
            id="globalConfirmModal" 
            class="fixed inset-0 z-[150] hidden items-center justify-center overflow-y-auto"
        >
            <!-- Overlay -->
            <div 
                onclick="window.closeGlobalConfirm()" 
                class="fixed inset-0 bg-black/60 backdrop-blur-sm"
            ></div>

            <!-- Modal Content -->
            <div class="relative bg-surface rounded-2xl max-w-md w-full mx-4 p-6 shadow-2xl border border-border/80 z-10 space-y-6">
                <div class="flex items-start gap-4">
                    <!-- Alert Warning Icon -->
                    <div class="shrink-0 w-10 h-10 rounded-full bg-rose-50 dark:bg-rose-950/30 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div class="space-y-1">
                        <h3 id="globalConfirmTitle" class="font-display text-headline-sm text-primary">Konfirmasi</h3>
                        <p id="globalConfirmMessage" class="text-body-md text-tertiary">Apakah Anda yakin?</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button 
                        onclick="window.closeGlobalConfirm()"
                        class="px-5 py-2.5 rounded-full border border-border text-on-surface hover:bg-neutral transition-colors text-label-lg font-semibold"
                    >
                        Batal
                    </button>
                    <button 
                        id="globalConfirmBtn"
                        class="px-5 py-2.5 rounded-full bg-rose-600 hover:bg-rose-700 text-surface transition-colors text-label-lg font-semibold"
                    >
                        Ya, Batalkan
                    </button>
                </div>
            </div>
        </div>

        <script>
            // Global Toast Notification function
            window.showNotification = function(type, message) {
                const container = document.getElementById('globalToastContainer');
                if (!container) return;
                const id = Date.now();
                
                let bgClass = '';
                let iconSvg = '';
                let progressBg = '';
                
                if (type === 'success') {
                    bgClass = 'bg-emerald-50/95 border-emerald-200 text-emerald-800 dark:bg-emerald-950/95 dark:border-emerald-800 dark:text-emerald-200';
                    iconSvg = '<svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    progressBg = 'bg-emerald-500';
                } else if (type === 'error' || type === 'danger') {
                    bgClass = 'bg-rose-50/95 border-rose-200 text-rose-800 dark:bg-rose-950/95 dark:border-rose-800 dark:text-rose-200';
                    iconSvg = '<svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
                    progressBg = 'bg-rose-500';
                } else if (type === 'warning') {
                    bgClass = 'bg-amber-50/95 border-amber-200 text-amber-800 dark:bg-amber-950/95 dark:border-amber-800 dark:text-amber-200';
                    iconSvg = '<svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
                    progressBg = 'bg-amber-500';
                } else {
                    bgClass = 'bg-sky-50/95 border-sky-200 text-sky-800 dark:bg-sky-950/95 dark:border-sky-800 dark:text-sky-200';
                    iconSvg = '<svg class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    progressBg = 'bg-sky-500';
                }

                const toast = document.createElement('div');
                toast.id = `toast-${id}`;
                toast.className = `relative flex gap-3 p-4 rounded-xl border shadow-lg backdrop-blur-md pointer-events-auto overflow-hidden transition-all duration-300 transform translate-x-full opacity-0 ${bgClass}`;
                toast.innerHTML = `
                    <div class="shrink-0">${iconSvg}</div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold leading-5">${message}</p>
                    </div>
                    <button onclick="document.getElementById('toast-${id}').remove()" class="shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="absolute bottom-0 left-0 h-1 ${progressBg}" style="width: 100%; transition: width 5000ms linear;"></div>
                `;
                
                container.appendChild(toast);
                
                setTimeout(() => {
                    toast.classList.remove('translate-x-full', 'opacity-0');
                    toast.classList.add('translate-x-0', 'opacity-100');
                    const progress = toast.querySelector('div:last-child');
                    if (progress) progress.style.width = '0%';
                }, 50);
                
                setTimeout(() => {
                    if (document.getElementById(`toast-${id}`)) {
                        toast.classList.remove('translate-x-0', 'opacity-100');
                        toast.classList.add('translate-x-full', 'opacity-0');
                        setTimeout(() => toast.remove(), 300);
                    }
                }, 5000);
            };

            // Global Confirmation Modal function
            let globalConfirmCallback = null;

            window.showConfirm = function(title, message, callback) {
                document.getElementById('globalConfirmTitle').textContent = title;
                document.getElementById('globalConfirmMessage').textContent = message;
                globalConfirmCallback = callback;
                
                const modal = document.getElementById('globalConfirmModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            window.closeGlobalConfirm = function() {
                const modal = document.getElementById('globalConfirmModal');
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                globalConfirmCallback = null;
            };

            document.getElementById('globalConfirmBtn').addEventListener('click', () => {
                if (globalConfirmCallback) {
                    globalConfirmCallback();
                }
                window.closeGlobalConfirm();
            });

            // Trigger session alerts automatically on page load
            document.addEventListener('DOMContentLoaded', () => {
                @if(session('success')) window.showNotification('success', '{{ session('success') }}'); @endif
                @if(session('error')) window.showNotification('error', '{{ session('error') }}'); @endif
                @if(session('info')) window.showNotification('info', '{{ session('info') }}'); @endif
            });
        </script>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
