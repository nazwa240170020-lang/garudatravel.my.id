

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Only start Alpine if Livewire is not present/running to prevent clashing.
document.addEventListener('DOMContentLoaded', () => {
    if (!document.querySelector('[wire\\:id]') && !window.Livewire) {
        Alpine.start();
    }
});
