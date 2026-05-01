<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
        protected $fillable = [
        'name',
        'description',
        'price',
        'discount',
        'quantity',
        'image',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];
}
