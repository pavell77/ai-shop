<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentMethodFactory> */
    use HasFactory;
    
    protected $guarded = [];

    /**
     * Отримати всі замовлення, оплачені цим способом.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'payment_method_id');
    }
}