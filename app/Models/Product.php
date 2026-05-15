<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable = ['name', 'image_id', 'sku', 'description', 'price', 'discount', 'quantity', 'image', 'is_active', 'is_deleted'];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['is_deleted'];

    /**
     * This tells Laravel to ADD this field to JSON response
     */
    protected $appends = ['price_after_discount'];

    /**
     * Accessor (calculated field)
     */
    public function getPriceAfterDiscountAttribute()
    {
        if (!$this->discount) {
            return (float) $this->price;
        }

        return round($this->price * (1 - $this->discount / 100), 2);
    }

    public function image()
    {
        return $this->belongsTo(Image::class, 'image_id');
    }

    public function bookingItems()
    {
        return $this->hasMany(BookingItem::class);
    }
}
