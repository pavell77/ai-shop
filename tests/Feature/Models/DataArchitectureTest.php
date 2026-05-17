<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('category factory can create a category with valid fields', function () {
    $category = Category::factory()->create();

    expect($category->name)->toBeString();
    expect($category->slug)->toBeString();
    expect($category->image_path)->toStartWith('categories/');
    expect($category->is_active)->toBeTrue();
});

test('product factory can create a product linked to a category', function () {
    $product = Product::factory()->create();

    // Виправлено: toBeInstanceOf замість toBeAnInstanceOf
    expect($product->category)->toBeInstanceOf(Category::class);
    expect($product->sku)->toBeString();
    expect($product->image_path)->toStartWith('products/');
    expect($product->price)->toBeGreaterThan(0);
    
    // Приведення до float для коректного порівняння decimal-полів
    expect((float) $product->cost_price)->toBeLessThan((float) $product->price);
});

test('category has many products relationship works', function () {
    $category = Category::factory()->create();
    
    Product::factory()->count(3)->create([
        'category_id' => $category->id
    ]);

    // Оновлюємо зв'язки в пам'яті
    $category->refresh();

    expect($category->products)->toHaveCount(3);
    // Виправлено: toBeInstanceOf замість toBeAnInstanceOf
    expect($category->products->first())->toBeInstanceOf(Product::class);
});