<?php

use App\Models\Product;
use App\Models\Category;
use Livewire\Volt\Volt;

it('can access products index page', function () {
    $response = $this->get(route('products.index'));
    
    $response->assertStatus(200);
});

it('displays products list on the index page', function () {
    $product = Product::factory()->create(['name' => 'Унікальний ШІ Модуль']);

    Volt::test('products.index')
        ->assertSee('Унікальний ШІ Модуль');
});

it('can filter products by category via url state', function () {
    $categoryA = Category::factory()->create(['slug' => 'processors']);
    $categoryB = Category::factory()->create(['slug' => 'ram']);

    $productA = Product::factory()->create(['category_id' => $categoryA->id, 'name' => 'Intel Core i9']);
    $productB = Product::factory()->create(['category_id' => $categoryB->id, 'name' => 'Kingston Fury']);

    // Сетімо стан категорії і перевіряємо видимість товарів
    Volt::test('products.index', ['categorySlug' => 'processors'])
        ->assertSee('Intel Core i9')
        ->assertDontSee('Kingston Fury');
});

it('can search products by name query', function () {
    $productA = Product::factory()->create(['name' => 'Клавіатура Keychron']);
    $productB = Product::factory()->create(['name' => 'Мишка Logitech']);

    Volt::test('products.index', ['search' => 'Keychron'])
        ->assertSee('Клавіатура Keychron')
        ->assertDontSee('Мишка Logitech');
});

it('can render single product show page', function () {
    $product = Product::factory()->create();

    $response = $this->get(route('products.show', $product->slug));
    
    $response->assertStatus(200);
});

it('dispatches cart-updated event when item added to cart from list and detail page', function () {
    $product = Product::factory()->create();

    // Тест кліку в картці товару
    Volt::test('products.card', ['product' => $product])
        ->call('addToCart')
        ->assertDispatched('cart-updated');

    // Тест кліку на сторінці детального перегляду
    Volt::test('products.show', ['product' => $product])
        ->call('addToCart')
        ->assertDispatched('cart-updated');
});