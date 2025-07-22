<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    protected $fillable = [
    'user_id', 'full_name', 'address_line1', 'address_line2',
    'city', 'state', 'postal_code', 'country', 'phone'
];

public function user()
{
    return $this->belongsTo(User::class);
}
}
