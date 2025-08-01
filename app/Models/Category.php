<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
    ];

    /**
     * Get all products that belong to this category
     * One-to-Many relationship: One category has many products
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}