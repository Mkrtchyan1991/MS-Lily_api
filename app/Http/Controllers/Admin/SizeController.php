<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    /**
     * Display a listing of sizes
     */
    public function index()
    {
        return response()->json(Size::all());
    }

    /**
     * Display the specified size
     */
    public function show($id)
    {
        $size = Size::findOrFail($id);
        return response()->json($size);
    }

    /**
     * Store a newly created size
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sizes',
        ]);

        $size = Size::create([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Size created!', 'size' => $size], 201);
    }

    /**
     * Update the specified size
     */
    public function update(Request $request, $id)
    {
        $size = Size::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:sizes,name,' . $size->id,
        ]);

        $size->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Size updated!', 'size' => $size]);
    }

    /**
     * Remove the specified size
     */
    public function destroy($id)
    {
        $size = Size::findOrFail($id);
        $size->delete();

        return response()->json(['message' => 'Size deleted!']);
    }
}
