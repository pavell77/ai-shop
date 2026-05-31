<?php

use App\Services\CartService;
use Livewire\Volt\Component;
use Livewire\Attributes\On;

new class extends Component {
    public int $count = 0;

    public function mount(CartService $cart): void
    {
        $this->count = $cart->getTotalCount();
    }

    #[On('cart-updated')]
    public function updateCount(CartService $cart): void
    {
        $this->count = $cart->getTotalCount();
    }
}; ?>

{{-- Компонент синхронізує стан з Alpine.js при кожному завантаженні (враховуючи wire:navigate) --}}
<div x-data
     x-init="
        if (!Alpine.store('cart')) {
            Alpine.store('cart', { count: {{ $count }} });
        } else {
            Alpine.store('cart').count = {{ $count }};
        }
        $watch('$wire.count', value => { Alpine.store('cart').count = value })
     "
     style="display: none;">
</div>