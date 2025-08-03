<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Order;



class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;


    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'mobile_number',
        'country',
        'city',
        'postal_code',
        'address',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get all orders placed by the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}

