<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

// Перенаправлення з головної на товари
Route::redirect('/', '/products');

// Роути каталогу товарів
Volt::route('/products', 'products.index')->name('products.index');
Volt::route('/products/{product:slug}', 'products.show')->name('products.show');

// НАШ НОВИЙ РОУТ: Сторінка кошика (доступна всім)
Volt::route('/cart', 'pages.cart')->name('cart');