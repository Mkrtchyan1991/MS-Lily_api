<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of brands
     */
    public function index()
    {
        return response()->json(Brand::all());
    }

    /**
     * Display the specified brand
     */
    public function show($id)
    {
        $brand = Brand::findOrFail($id);
        return response()->json($brand);
    }

    /**
     * Store a newly created brand
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands',
        ]);

        $brand = Brand::create([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Brand created!', 'brand' => $brand], 201);
    }

    /**
     * Update the specified brand
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
        ]);

        $brand->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Brand updated!', 'brand' => $brand]);
    }

    /**
     * Remove the specified brand
     */
    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return response()->json(['message' => 'Brand deleted!']);
    }
}
