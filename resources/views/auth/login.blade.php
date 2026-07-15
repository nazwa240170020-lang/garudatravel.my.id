@extends('layouts.app')

@section('navigation')
@endsection

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="card p-8 max-w-sm w-full">

        {{-- Logo + Brand --}}
        <div class="text-center mb-6">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto text-primary">
                <path d="M21 16v-2l-8-5V3.5a1.5 1.5 0 00-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
            </svg>
            <h1 class="font-display text-headline-md text-primary mt-2">Garuda</h1>
        </div>

        {{-- Headline --}}
        <h2 class="font-display text-headline-sm text-primary mb-1">Masuk</h2>
        <p class="text-body-sm text-tertiary mb-6">Pesan tiket penerbangan impianmu sekarang</p>

        {{-- Session Status --}}
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-4">
                <label for="email" class="text-label-sm text-tertiary mb-1 block">Email</label>
                <div class="relative">
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="contoh@email.com"
                        class="input-field @error('email') border-error @enderror pl-10">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-tertiary pointer-events-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                @error('email')
                    <p class="text-body-sm text-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-4">
                <label for="password" class="text-label-sm text-tertiary mb-1 block">Kata Sandi</label>
                <div class="relative">
                    <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan kata sandi"
                        class="input-field @error('password') border-error @enderror pl-10">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-tertiary pointer-events-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </div>
                @error('password')
                    <p class="text-body-sm text-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember & Forgot --}}
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" id="remember_me" class="accent-primary rounded border-border">
                    <span class="text-body-sm text-tertiary">Ingat saya</span>
                </label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-nav-link text-primary hover:text-secondary transition-colors">Lupa kata sandi?</a>
                @endif
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-primary w-full">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M13 12H3"/>
                </svg>
                Masuk
            </button>
        </form>

        {{-- Register Link --}}
        @if(Route::has('register'))
            <p class="text-center mt-4 text-body-sm text-tertiary">
                Belum punya akun? <a href="{{ route('register') }}" class="text-nav-link text-primary hover:text-secondary transition-colors">Daftar di sini</a>
            </p>
        @endif

    </div>
</div>
@endsection
