<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt; // Фасад уже імпортовано, супер

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

// ЗМІНЮЄМО ТУТ: Замість Route::volt пишемо Volt::route
Volt::route('/products', 'products.index')->name('products.index');

// І ТУТ ТАКОЖ:
Volt::route('/products/{product:slug}', 'products.show')->name('products.show');