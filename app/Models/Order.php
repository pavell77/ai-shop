<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    public function user() { return $this->belongsTo(User::class); }

    public function deliveryMethod() { return $this->belongsTo(DeliveryMethod::class); }

    public function paymentMethod() { return $this->belongsTo(PaymentMethod::class); }

    public function items() { return $this->hasMany(OrderItem::class); }
    
    public function transactions() { return $this->hasMany(Transaction::class); }
}
