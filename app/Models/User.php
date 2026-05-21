<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,HasApiTokens,HasRoles;

        protected string $guard_name = "passport";

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
         'name',
        'email',
        'personal_id',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
 'password',
 'is_deleted',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
                'created_at' => 'datetime:Y-m-d H:i:s',
                'updated_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /**
 * User has many product like records.
 */
public function productLikes()
{
    return $this->hasMany(ProductLike::class, 'user_id', 'id');
}

/**
 * User has many liked products through product_likes table.
 */
public function likedProducts()
{
    return $this->belongsToMany(
        Product::class,
        'product_likes',
        'user_id',
        'product_id'
    )
        ->wherePivot('like', true)
        ->withPivot('like')
        ->withTimestamps();
}

    public function bookings()
{
    return $this->hasMany(Booking::class);
}
}
