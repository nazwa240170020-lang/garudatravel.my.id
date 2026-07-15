@props([
    'variant' => 'accent',
    'href' => null,
    'type' => 'button',
    'size' => 'default',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-medium transition-colors';

    $variants = [
        'primary' => 'bg-primary text-surface border border-transparent rounded-full hover:bg-primary-hover shadow-sm',
        'secondary' => 'bg-transparent text-primary border border-primary rounded-full hover:bg-primary-tint',
        'accent' => 'bg-accent text-surface border border-transparent rounded-full hover:bg-accent-hover shadow-sm',
        'tertiary' => 'bg-transparent text-on-surface hover:text-primary p-0',
    ];

    $sizes = [
        'default' => 'px-6 py-2.5 text-label-lg h-10',
        'sm' => 'px-4 py-1.5 text-label-md h-8',
        'lg' => 'px-8 py-3.5 text-body-lg h-12',
    ];

    $class = trim("$base {$variants[$variant]} {$sizes[$size]}");
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $class]) }}>
        {{ $slot }}
    </button>
@endif
