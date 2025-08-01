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
    // Diese methoden bringen alle Produkte und zeigt mit erweiterten Filtern
    public function index(Request $request)
    {
        //with bringt die produkte mit Kategorie, Brand und Tags
        $query = Product::with(['category', 'brand', 'tags']);

        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Filter by brand
        if ($request->has('brand') && $request->brand != '') {
            $query->where('brand_id', $request->brand);
        }

        // Filter by tag
        if ($request->has('tag') && $request->tag != '') {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag);
            });
        }

        // Filter by multiple tags (comma-separated)
        if ($request->has('tags') && $request->tags != '') {
            $tagIds = explode(',', $request->tags);
            $query->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }

        // Filter by price range
        if ($request->has('price_min') && $request->price_min != '') {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->has('price_max') && $request->price_max != '') {
            $query->where('price', '<=', $request->price_max);
        }

        // Filter by color
        if ($request->has('color') && $request->color != '') {
            $query->where('color', 'like', '%' . $request->color . '%');
        }

        // Filter by size
        if ($request->has('size') && $request->size != '') {
            $query->where('size', $request->size);
        }

        // Search by name or description
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter by stock availability
        if ($request->has('in_stock') && $request->in_stock == 'true') {
            $query->where('stock', '>', 0);
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at'); // default: newest first
        $sortOrder = $request->get('sort_order', 'desc'); // default: descending

        // Validate sort parameters
        $allowedSortFields = ['name', 'price', 'created_at', 'stock'];
        $allowedSortOrders = ['asc', 'desc'];

        if (in_array($sortBy, $allowedSortFields) && in_array($sortOrder, $allowedSortOrders)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest(); // fallback to default sorting
        }

        // Pagination - allow custom per_page parameter
        $perPage = $request->get('per_page', 10);
        $perPage = min(max($perPage, 1), 50); // limit between 1 and 50

        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    // diese methode return Produkte mit id,wenn die Produkt nicht gefunden wird soll 404 angezeit werden
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
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
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
            'price' => $request->price,
            'stock' => $request->stock,
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
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
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
            'price' => $request->price,
            'stock' => $request->stock,
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



    // Optional: Get filter options with product counts
    public function getFilterOptions()
    {
        $categories = Category::withCount('products')->get();
        $brands = Brand::withCount('products')->get();
        $tags = Tag::withCount('products')->get();

        // Get price range
        $priceRange = Product::selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();

        // Get available colors and sizes
        $colors = Product::whereNotNull('color')->distinct()->pluck('color');
        $sizes = Product::whereNotNull('size')->distinct()->pluck('size');

        return response()->json([
            'categories' => $categories,
            'brands' => $brands,
            'tags' => $tags,
            'price_range' => $priceRange,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }
}