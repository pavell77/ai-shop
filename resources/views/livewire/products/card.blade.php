<?php

use Livewire\Volt\Component;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Support\Facades\Session; // Імпортуємо фасад для "Laravel Дзен"

new class extends Component {

    public Product $product;

    public function mount(Product $product): void
    {
        $this->product = $product->load(['images', 'category']);
    }

    public function addToCart(CartService $cart): void
    {
        // 1. Додаємо товар до бази даних через сервіс
        $cart->add($this->product->id, 1);

        // 2. Сповіщаємо лічильник у шапці, щоб він перемалював цифру
        $this->dispatch('cart-updated');

        // 3. Зберігаємо флеш-повідомлення через чистий статичний фасад
        Session::flash('success', "{$this->product->name} додано в кошик!");
    }
}; ?>

<div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden flex flex-col justify-between group transition hover:shadow-md">
    <a href="{{ route('products.show', $product->slug) }}" class="block overflow-hidden bg-gray-100 aspect-square relative">
        @if($product->images->first())
            <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                 alt="{{ $product->name }}" 
                 class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400">
                <span>Немає фото</span>
            </div>
        @endif
    </a>

    <div class="p-4 flex-grow flex flex-col justify-between">
        <div>
            <span class="text-xs font-medium text-indigo-600 uppercase tracking-wider">
                {{ $product->category->name }}
            </span>
            <a href="{{ route('products.show', $product->slug) }}" class="block mt-1">
                <h3 class="text-sm font-semibold text-gray-950 hover:text-indigo-600 transition line-clamp-2">
                    {{ $product->name }}
                </h3>
            </a>
            <p class="mt-2 text-xs text-gray-500 line-clamp-2">
                {{ $product->description }}
            </p>
        </div>

        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
            <span class="text-base font-bold text-gray-900">
                {{ number_format($product->price, 2) }} ₴
            </span>
            
            <button type="button" 
                    x-data="{ loading: false }"
                    x-bind:disabled="loading"
                    x-on:click.prevent.stop="loading = true; $wire.addToCart().then(() => loading = false)"
                    class="rounded bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-600 shadow-sm hover:bg-indigo-100 transition disabled:opacity-50">
                
                <span x-show="!loading">В кошик</span>
                <span x-show="loading" style="display: none;">Додаю...</span>
            </button>
        </div>
    </div>
</div>