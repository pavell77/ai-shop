<?php

use App\Services\CartService;
use Livewire\Volt\Component;

new class extends Component {
    public $items = [];
    public float $totalPrice = 0.00;

    public function mount(CartService $cartService): void
    {
        $this->loadCart($cartService);
    }

    private function loadCart(CartService $cartService): void
    {
        $this->items = $cartService->getItems();
        $this->totalPrice = $cartService->getTotalPrice();
    }

    public function increment(int $productId, CartService $cartService): void
    {
        $cartService->add($productId, 1);
        $this->dispatch('cart-updated');
        $this->loadCart($cartService);
    }

    public function decrement(int $productId, CartService $cartService): void
    {
        $cartItem = collect($this->items)->first(function($item) use ($productId) {
            return isset($item->product) && $item->product->id === $productId;
        });

        if ($cartItem && $cartItem->quantity > 1) {
            $cartService->add($productId, -1);
        } else {
            $cartService->remove($productId);
        }
        
        $this->dispatch('cart-updated');
        $this->loadCart($cartService);
    }

    public function removeItem(int $productId, CartService $cartService): void
    {
        $cartService->remove($productId);
        $this->dispatch('cart-updated');
        $this->loadCart($cartService);
    }

    public function clearCart(CartService $cartService): void
    {
        $cartService->clear();
        $this->dispatch('cart-updated');
        $this->loadCart($cartService);
    }
}; ?>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start p-6 text-gray-900 dark:text-gray-100">
    
    {{-- ЛІВА ЧАСТИНА: СПИСОК ТОВАРІВ --}}
    <div class="lg:col-span-8 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
        @if(blank($items) || (method_exists($items, 'isEmpty') && $items->isEmpty()))
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 1.75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                <h3 class="mt-4 text-sm font-semibold">Ваш кошик порожній</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ви ще не додали жодного товару.</p>
                <div class="mt-6">
                    <a href="{{ route('products.index') }}" wire:navigate class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition">
                        Повернутися до покупок
                    </a>
                </div>
            </div>
        @else
            <div class="flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700 mb-6">
                <h3 class="text-lg font-medium">Елементи кошика</h3>
                <button wire:click="clearCart" class="text-sm font-medium text-red-600 hover:text-red-500 transition">
                    Очистити кошик
                </button>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($items as $item)
                    @php
                        $product = $item->product ?? $item;
                        $quantity = $item->quantity ?? 1;
                        $productImage = $product->images?->first()?->image_path ?? null;
                    @endphp
                    <div class="flex py-6 first:pt-0 last:pb-0 items-center justify-between gap-4">
                        <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50">
                            @if($productImage)
                                <img src="{{ asset('storage/' . $productImage) }}" alt="{{ $product->name }}" class="h-full w-full object-cover object-center">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-xs text-gray-400">Ні</div>
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col sm:flex-row sm:justify-between gap-2">
                            <div class="max-w-xs">
                                <h4 class="text-sm font-medium">{{ $product->name }}</h4>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ number_format($product->price, 2) }} ₴ / шт.
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded bg-gray-50 dark:bg-gray-700">
                                    <button wire:click="decrement({{ $product->id }})" type="button" class="px-2 py-1 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-l transition">
                                        &minus;
                                    </button>
                                    <span class="px-3 py-1 text-sm font-medium min-w-[2rem] text-center">
                                        {{ $quantity }}
                                    </span>
                                    <button wire:click="increment({{ $product->id }})" type="button" class="px-2 py-1 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-r transition">
                                        &plus;
                                    </button>
                                </div>

                                <button wire:click="removeItem({{ $product->id }})" type="button" class="p-1.5 text-gray-400 hover:text-red-500 transition ml-2">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="text-right pl-4">
                            <span class="text-sm font-semibold">
                                {{ number_format($product->price * $quantity, 2) }} ₴
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ПРАВА ЧАСТИНА: БЛОК ПІДСУМКУ --}}
    @if(!(blank($items) || (method_exists($items, 'isEmpty') && $items->isEmpty())))
        <div class="lg:col-span-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm sticky top-6">
            <h3 class="text-lg font-medium mb-6">Разом</h3>
            
            <div class="space-y-4">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                    <span>Вартість товарів</span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($totalPrice, 2) }} ₴</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                    <span>Доставка</span>
                    <span class="text-green-600 font-medium">Безкоштовно</span>
                </div>
                
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-baseline">
                    <span class="text-base font-medium">До сплати</span>
                    <span class="text-2xl font-bold">{{ number_format($totalPrice, 2) }} ₴</span>
                </div>
            </div>

            {{-- Кнопка скруглена і розміщена по центру --}}
            <div class="mt-6 flex flex-col items-center">
                <a href="{{ route('checkout') }}" wire:navigate class="w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition shadow-lg shadow-indigo-600/20">
                    Оформити замовлення
                </a>
            </div>

            {{-- Кнопка повернення до покупок --}}
            <div class="mt-4 text-center">
                <a href="{{ route('products.index') }}" 
                   wire:navigate 
                   class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition group">
                    <svg class="h-4 w-4 transform group-hover:-translate-x-0.5 transition duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Повернутися до покупок
                </a>
            </div>
        </div>
    @endif
</div>