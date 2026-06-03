<?php

use App\Models\Product;
use App\Services\CartService;
use Livewire\Volt\Volt;
use Illuminate\Foundation\Testing\RefreshDatabase;

// Використовуємо очищення БД між тестами, щоб фейкові товари не накопичувалися
uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| ТЕСТИ ОПЕРАЦІЇ ЗМЕНШЕННЯ (DECREMENT)
|--------------------------------------------------------------------------
*/

it('decrements product quantity in cart if it is greater than 1', function () {
    // Arrange: Створюємо товар і додаємо 2 одиниці в кошик
    $product = Product::factory()->create();
    $cartService = app(CartService::class);
    $cartService->add($product->id, 2);

    // Act: Викликаємо мінус у Volt-компоненті
    Volt::test('pages.cart')
        ->call('decrement', $product->id)
        ->assertDispatched('cart-updated');

    // Assert: Товар лишився, але кількість тепер 1
    $cartItems = $cartService->getItems();
    expect($cartItems)->not->toBeEmpty();
    expect($cartItems->first()->quantity)->toBe(1);
});

it('removes product from cart completely when decrementing from quantity of 1', function () {
    // Arrange: Створюємо товар і додаємо всього 1 одиницю
    $product = Product::factory()->create();
    $cartService = app(CartService::class);
    $cartService->add($product->id, 1);

    // Act: Тиснемо мінус
    Volt::test('pages.cart')
        ->call('decrement', $product->id);

    // Assert: Кошик став повністю порожнім
    expect($cartService->getItems())->toBeEmpty();
});

/*
|--------------------------------------------------------------------------
| ТЕСТИ ОПЕРАЦІЇ ЗБІЛЬШЕННЯ (INCREMENT)
|--------------------------------------------------------------------------
*/

it('increments product quantity in cart successfully', function () {
    // Arrange: Додаємо 1 товар у кошик
    $product = Product::factory()->create();
    $cartService = app(CartService::class);
    $cartService->add($product->id, 1);

    // Act: Тиснемо плюс (викликаємо твій метод increment)
    Volt::test('pages.cart')
        ->call('increment', $product->id)
        ->assertDispatched('cart-updated');

    // Assert: Кількість товару в кошику має збільшитися до 2
    $cartItems = $cartService->getItems();
    expect($cartItems->first()->quantity)->toBe(2);
});

/*
|--------------------------------------------------------------------------
| ТЕСТИ ПОВНОГО ВИДАЛЕННЯ (REMOVE ITEM)
|--------------------------------------------------------------------------
*/

it('removes product from cart completely using remove button', function () {
    // Arrange: Додаємо товар у будь-якій кількості (наприклад, 3 штуки)
    $product = Product::factory()->create();
    $cartService = app(CartService::class);
    $cartService->add($product->id, 3);

    // Act: Клікаємо на кнопку видалення (іконка смітника)
    // Переконайся, що метод у Volt-компоненті називається саме removeItem
    Volt::test('pages.cart')
        ->call('removeItem', $product->id)
        ->assertDispatched('cart-updated');

    // Assert: Товар повністю зник, незважаючи на те, що його було багато
    expect($cartService->getItems())->toBeEmpty();
});