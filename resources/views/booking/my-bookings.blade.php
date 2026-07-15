@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-10">
    <h1 class="font-display text-headline-lg text-primary mb-6">My Bookings</h1>

    @if(session('success'))
        <div class="flex items-start gap-3 border rounded-md p-4 mb-5 text-body-sm bg-green-100 text-green-700 border-green-400">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-start gap-3 bg-error/10 border border-error/30 rounded-md p-4 mb-5 text-body-sm text-error">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <livewire:my-bookings />
</div>
@endsection
