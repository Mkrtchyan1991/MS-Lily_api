<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Get all products that have this tag
     * Many-to-Many relationship: One tag can belong to many products
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}