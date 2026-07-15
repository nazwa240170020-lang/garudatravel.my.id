@props([
    'padding' => '4',
])

@php
    $paddings = [
        '4' => 'p-4',
        '6' => 'p-6',
        '8' => 'p-8',
        '10' => 'p-10',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'bg-surface border border-border rounded-lg shadow-sm ' . ($paddings[$padding] ?? 'p-4')]) }}>
    {{ $slot }}
</div>
