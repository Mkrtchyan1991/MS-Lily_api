<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'brand_id',
        'color',
        'size',
        'image',
        'price',
        'stock'
    ];

    public function category() {
        //Dieses Produkt gehört zu einer Kategorie-Viele Produkte können zu einer Kategorie gehören 
        return $this->belongsTo(Category::class);  // one-to-many
    }

    public function brand() {
        // Dieses Produkt gehört zu einer Marke
        return $this->belongsTo(Brand::class);  // one-to-many
    }

    public function tags() {
        //Das Produkt kann mehrere Tags haben, und jedes Tag kann zu mehreren Produkten gehören
        return $this->belongsToMany(Tag::class);  // many-to-many
    }
    //Gibt alle Benutzer zurück, die dieses Produkt als Favorit markiert haben.
    public function favoritedBy()
   {
    return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
   }
}