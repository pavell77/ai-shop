<?php

use Livewire\Volt\Component;
use App\Models\Product;

new class extends Component {
    public Product $product;

    // Вказуємо макет для повносторінкового компонента
    public function boot(): void
    {
        //$this->layout('layouts.app');
    }

    public function mount(Product $product): void
    {
        $this->product = $product->load(['images', 'category']);
    }

    public function addToCart(): void
    {
        $this->dispatch('cart-updated');
        session()->flash('success', "{$this->product->name} додано в кошик!");
    }
}; ?>

<div class="bg-white rounded-xl border border-gray-200 p-6 md:p-8 shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Галерея зображень -->
        <div class="space-y-4">
            <div class="aspect-square bg-gray-50 rounded-lg overflow-hidden border border-gray-100">
                @if($product->images->first())
                    <img src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                         alt="{{ $product->name }}" 
                         class="w-full h-full object-cover object-center">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <span>Немає зображення</span>
                    </div>
                @endif
            </div>
            
            <!-- Додаткова галерея (якщо зображень > 1) -->
            @if($product->images->count() > 1)
                <div class="grid grid-cols-4 gap-2">
                    @foreach($product->images as $img)
                        <div class="aspect-square bg-gray-50 rounded border overflow-hidden cursor-pointer hover:border-indigo-500 transition">
                            <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Інформаційна панель -->
        <div class="flex flex-col justify-between">
            <div>
                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                    {{ $product->category->name }}
                </span>
                
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 mt-3">
                    {{ $product->product_name ?? $product->name }}
                </h1>
                
                <p class="text-2xl font-semibold text-indigo-600 mt-4">
                    {{ number_format($product->price, 2) }} ₴
                </p>

                <div class="mt-6 border-t border-gray-100 pt-6">
                    <h3 class="text-sm font-medium text-gray-900">Опис товару</h3>
                    <div class="mt-2 text-sm text-gray-600 space-y-4 leading-relaxed">
                        {{ $product->description }}
                    </div>
                </div>
            </div>

            <div class="mt-8 border-t border-gray-100 pt-6">
                @if(session()->has('success'))
                    <div class="p-3 mb-4 text-sm text-green-800 rounded-md bg-green-50">
                        {{ session('success') }}
                    </div>
                @endif

                <button type="button" 
                        wire:click="addToCart" 
                        class="w-full flex items-center justify-center rounded-md bg-indigo-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    Додати до кошика
                </button>
            </div>

        </div>
    </div>
</div>