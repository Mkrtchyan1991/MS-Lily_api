<?php

namespace App\Http\Controllers;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\User;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, $productId)
    {
        //Eingelogter Nutzer
        $user = auth()->user(); 
        //Überprüft,ob diese Produkt existiert
        $product = Product::findOrFail($productId);

        //Wechelt auf Favoritestatus
        $user->favoriteProducts()->toggle($productId);

        return response()->json(['message' => 'Favorite updated successfully']);
    }

    public function getFavorites()
    {
       //Eingelogter Nutzer
        $user = auth()->user();
       //Hole die Lieblingsprodukte des Benutzers zusammen mit den Kategorien, Marken und Tags“
        $favorites = $user->favoriteProducts()->with(['category', 'brand', 'tags'])->get();

        return ProductResource::collection($favorites);
    }

    //Löschen von Favorites
    public function remove($productId)
    {
    //Eingelogter Nutzer
    $user = auth()->user();
    //Finden wir das Produkt,wenn es gips nicht soll 404 Fehler zeigen
    $product = Product::findOrFail($productId);
    //Trent die Verbindung Nutzers und Produkt in der Tabelle
    $user->favoriteProducts()->detach($product->id);
    //Gipt zurück als JSON format
    return response()->json(['message' => 'Removed from favorites']);
   }
}
