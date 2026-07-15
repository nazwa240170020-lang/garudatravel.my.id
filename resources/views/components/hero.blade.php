@props([
    'title' => '',
    'description' => '',
    'image' => null,
    'action' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col lg:flex-row items-center gap-26 lg:gap-140 py-140']) }}>
    <div class="flex-1 space-y-6">
        @if($title)
            <h1 class="font-display text-headline-display text-primary leading-tight">{{ $title }}</h1>
        @endif
        @if($description)
            <p class="text-body-lg text-tertiary max-w-xl">{{ $description }}</p>
        @endif
        @if($action)
            <div class="flex gap-3">
                {{ $action }}
            </div>
        @endif
        {{ $slot ?? '' }}
    </div>
    @if($image)
        <div class="flex-1 flex justify-center">
            <img src="{{ $image }}" alt="" class="w-full max-w-lg object-contain">
        </div>
    @endif
</div>
