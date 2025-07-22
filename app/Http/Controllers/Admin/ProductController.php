<?php

//Diese Datei gehört zum namensraum-admin,das ist wichtig,damit laravel den Controller richtig finden kann
namespace App\Http\Controllers\Admin;

// Model für Produkte aus der Datenbank
use App\Models\Product;
// Produkt hat eine Kategorie
use App\Models\Category;
// Produkt hat eine Kategorie
use App\Models\Brand;
// Produkt hat eine Kategorie
use App\Models\Tag;
//Basis Controller von laravel
use App\Http\Controllers\Controller;
// Request empfangt Formularen und APIs
use Illuminate\Http\Request;

//wir erstellen neue ProductController,der von Laravel bassis-classe erbt(jarangel)
class ProductController extends Controller
{
    // Diese methoden bringen alle Produkte und zeigt
    public function index()
    {
        //with bringt die produkte mit Kategorie
        //latest brigt die letze hinzufügte Produkt
        $products = Product::with(['category', 'brand', 'tags'])->latest()->paginate(10);
        return response()->json($products);
    }

    // diese methode return Produkte mit id,wenn die Produkt  nicht gefunden wird soll 404 angezeit werden
    public function show($id)
    {
        $product = Product::with(['category', 'brand', 'tags'])->findOrFail($id);
        return response()->json($product);
    }

    // wir stellen Produkte mit Validation und wird $request gespeichert
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',//soll die produkte die id-s haben in unter die kategories
            'brand_id' => 'required|exists:brands,id',
            'description' => 'nullable|string',//muss nicht sein,wenn gips muss string sein
            'color' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:20',
            'tags' => 'nullable|array',//wenn gips,soll arr sein
            'tags.*' => 'exists:tags,id',//jeder tag id muss in tags tebele sein
            'image' => 'nullable|image|max:2048',
        ]);

        // Hier wird geprüft, ob eine Bilddatei mitgeschickt wurde. Falls ja, wird sie im Ordner storage/app/public/products/ gespeichert, und der Pfad wird zurückgegeben. Falls nicht, bleibt der Pfad null.
        $path = $request->hasFile('image')
            ? $request->file('image')->store('products', 'public')
            : null;

        //  Erstellt ein neues Produkt in der Datenbank
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'color' => $request->color,
            'size' => $request->size,
            'image' => $path,
        ]);
         //verbindet die Produkte mit tags, many-to-many 
        $product->tags()->sync($request->tags ?? []);
        // ergebnisse zeigt mit JSON format-produkt created
        return response()->json(['message' => 'Product created!', 'product' => $product], 201);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:20',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'image' => 'nullable|image|max:2048',
        ]);
        
        //zuerst nehmen wir die alte route von bild($product->image) wenn mit form neue bild ist geschickt 
        //wenn keine neues Bild hochgeladen wurde,bleibt der alte Pfand erhalten
        $path = $product->image;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
        }
        //Das Produkt wird in Datenbank aktualisiert min neuen werten aus dem Request
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
            'color' => $request->color,
            'size' => $request->size,
            'image' => $path,
        ]);
        //Aktualisiert die tags von der Produkte,wenn es gips keine die verbindung wird gelöscht.Das ist für many-to-many
        $product->tags()->sync($request->tags ?? []);

        return response()->json(['message' => 'Product updated!', 'product' => $product]);
    }
    //diese function löscht die Daten von der Produkt
    public function destroy(Product $product)
    {
        // löscht die verbindung zwischen Produkt und tag von Product_tag
        $product->tags()->detach();
        //loschtv die daten von database
        $product->delete();
        
        return response()->json(['message' => 'Product deleted!']);
    }
    //diese methode lässt die Produkte filtern
    public function filterByTag(Request $request)
    {
        //holt den tag wert aus der URL
        $tagId = $request->query('tag');
        //nur Produkte die dieses Tag haben
        $products = Product::whereHas('tags', function ($q) use ($tagId) {
            $q->where('tags.id', $tagId);
        })->with(['category', 'brand', 'tags'])->paginate(10);

        return response()->json($products);
    }

    //  Filter by brand ID
    public function filterByBrand(Request $request)
    {
        $brandId = $request->query('brand');
        $products = Product::where('brand_id', $brandId)
            ->with(['category', 'brand', 'tags'])->paginate(10); // wir laden die verknüpften Daten von Kategorien und wir bekommen max. 10 Produkte pro seite

        return response()->json($products);
    }

    
    public function getCategories()
    {
        //Gipt alle Kategorien zurück,hol alle Einträge aus der Tabele Category
        return response()->json(Category::all());
    }

    public function getBrands()
    {
        //Gipt alle Marken zurück
        return response()->json(Brand::all());
    }

    public function getTags()
    { 
        //Gipt alle Tags zurück
        return response()->json(Tag::all());
    }
}


