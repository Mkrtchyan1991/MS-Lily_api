<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'brands';

    protected $fillable = [
        'name',
    ];

    /**
     * Get all products that belong to this brand
     * One-to-Many relationship: One brand has many products
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}