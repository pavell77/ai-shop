<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Product;
use App\Models\Category;

new class extends Component {
    use WithPagination;


    // Синхронізація станів з URL фільтрами
    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'category', history: true)]
    public string $categorySlug = '';

    #[Url(as: 'sort', history: true)]
    public string $sortBy = 'latest';

    // Скидаємо пагінацію на першу сторінку при зміні фільтрів
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingCategorySlug(): void { $this->resetPage(); }
    public function updatingSortBy(): void { $this->resetPage(); }

    public function with(): array
    {
        $query = Product::query()
            ->with(['category', 'images' => fn($q) => $q->where('is_primary', true)]);

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if (!empty($this->categorySlug)) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', $this->categorySlug);
            });
        }

        $query = match ($this->sortBy) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default => $query->latest(),
        };

        return [
            'products' => $query->paginate(9),
            'categories' => Category::all(),
        ];
    }
}; ?>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <!-- Сайдбар фільтрів -->
    <aside class="space-y-6">
        <div>
            <h2 class="text-lg font-bold text-gray-900 mb-3">Пошук</h2>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Назва товару..." 
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        </div>

        <div>
            <h2 class="text-lg font-bold text-gray-900 mb-3">Категорії</h2>
            <div class="space-y-2 flex flex-col">
                <button wire:click="$set('categorySlug', '')" 
                        class="text-left text-sm py-1 px-2 rounded transition {{ empty($categorySlug) ? 'bg-indigo-600 text-white font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                    Всі категорії
                </button>
                @foreach($categories as $category)
                    <button wire:click="$set('categorySlug', '{{ $category->slug }}')" 
                            class="text-left text-sm py-1 px-2 rounded transition {{ $categorySlug === $category->slug ? 'bg-indigo-600 text-white font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>
    </aside>

    <!-- Основна сітка каталогу -->
    <section class="lg:col-span-3 space-y-6">
        <!-- Панель сортування -->
        <div class="flex items-center justify-between bg-white p-4 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-600">
                Знайдено товарів: <span class="font-bold text-gray-900">{{ $products->total() }}</span>
            </p>
            <div class="flex items-center space-x-2">
                <label for="sort" class="text-sm text-gray-600 shrink-0">Сортувати:</label>
                <select id="sort" 
                        wire:model.live="sortBy" 
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5">
                    <option value="latest">Новинки</option>
                    <option value="price_asc">Від дешевих до дорогих</option>
                    <option value="price_desc">Від дорогих до дешевих</option>
                </select>
            </div>
        </div>

        @if(session()->has('success'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <!-- Сітка -->
        @if($products->isEmpty())
            <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                <p class="text-gray-500">Товарів не знайдено за вказаними фільтрами.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach($products as $product)
                    <!-- Виклик Volt sub-компонента картки -->
                    <livewire:products.card :product="$product" :key="$product->id" />
                @endforeach
            </div>

            <!-- Слідкуємо за пагінацією через стандартні лінки Tailwind -->
            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @endif
    </section>
</div>