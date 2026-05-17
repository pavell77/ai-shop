<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    protected $casts = ['payload' => 'array']; // Автоматично кастимо JSON в масив
    
    public function order() { return $this->belongsTo(Order::class); }
}
