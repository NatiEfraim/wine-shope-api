<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    //
        protected $fillable = [
        'name',
        'type',
        'file_name',
        'path',
    ];

    public function product()
    {
        return $this->hasOne(Product::class);
    }
}
