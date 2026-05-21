<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductLike extends Model
{
    //
       protected $fillable = [
        'user_id',
        'product_id',
        'like',
    ];

    protected $casts = [
        'like' => 'boolean',
    ];

    /**
     * ProductLike belongs to one user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * ProductLike belongs to one product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
