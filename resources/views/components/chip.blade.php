@props([
    'active' => false,
])

@php
    $classes = $active
        ? 'bg-primary text-surface border-primary font-semibold'
        : 'bg-primary-tint text-primary border-transparent';
@endphp

<span {{ $attributes->merge(['class' => "chip {$classes}"]) }}>
    {{ $slot }}
</span>
