@props([
    'name',
    'label',
    'value' => ''
])

<div class="border border-border rounded-xl p-4 flex items-center gap-3 bg-surface hover:border-primary/50 transition-colors w-full">
    <span class="w-9 h-9 rounded-full bg-neutral text-primary flex items-center justify-center text-base shrink-0">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </span>
    <div class="flex-1 min-w-0">
        <div class="text-body-sm text-tertiary mb-0.5">{{ $label }}</div>
        <input
            type="date"
            name="{{ $name }}"
            value="{{ $value }}"
            class="w-full border-0 p-0 font-sans font-semibold text-on-surface focus:ring-0 text-body-lg bg-transparent cursor-pointer"
        >
    </div>
</div>
