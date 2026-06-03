<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryMethod extends Model
{
    /** @use HasFactory<\Database\Factories\DeliveryMethodFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * Отримати всі замовлення, доставлені цим способом.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'delivery_method_id');
    }
}