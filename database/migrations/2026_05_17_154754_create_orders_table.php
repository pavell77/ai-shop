<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_method_id')->constrained();
            $table->foreignId('payment_method_id')->constrained();
            $table->decimal('total_price', 10, 2);
            $table->string('status')->default('pending'); // pending, processing, completed, cancelled
            $table->string('delivery_status')->default('pending'); // pending, shipped, delivered
            $table->string('tracking_number')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('delivery_address');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('orders'); }
};
