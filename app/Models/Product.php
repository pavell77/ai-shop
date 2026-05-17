<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'image_path',
        'price',
        'cost_price',
        'quantity',
        'is_visible',
    ];

    /**
     * Отримати категорію, до якої належить товар.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images() { return $this->hasMany(ProductImage::class); }
}