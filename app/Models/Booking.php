<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    //
    protected $fillable = [
        'serial_number',
        'user_id',
        'status_id',
        'total_price',
        'is_deleted',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'is_deleted' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function item()
    {
        return $this->hasMany(BookingItem::class);
    }

}
