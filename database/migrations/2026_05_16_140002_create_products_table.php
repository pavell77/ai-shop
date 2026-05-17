<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // ERP-логіка: забороняємо видаляти категорію (onDelete restrict), якщо в ній є товари
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique(); // Артикул для ERP
            $table->text('description')->nullable();
            $table->string('image_path')->nullable(); // Тека завантаження: public/products/
            
            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable(); // Собівартість для фінансового обліку
            $table->integer('quantity')->default(0); // Складські залишки
            
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};