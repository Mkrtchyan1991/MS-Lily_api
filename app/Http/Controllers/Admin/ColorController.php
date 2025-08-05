<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    /**
     * Display a listing of colors
     */
    public function index()
    {
        return response()->json(Color::all());
    }

    /**
     * Display the specified color
     */
    public function show($id)
    {
        $color = Color::findOrFail($id);
        return response()->json($color);
    }

    /**
     * Store a newly created color
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:colors',
        ]);

        $color = Color::create([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Color created!', 'color' => $color], 201);
    }

    /**
     * Update the specified color
     */
    public function update(Request $request, $id)
    {
        $color = Color::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:colors,name,' . $color->id,
        ]);

        $color->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Color updated!', 'color' => $color]);
    }

    /**
     * Remove the specified color
     */
    public function destroy($id)
    {
        $color = Color::findOrFail($id);
        $color->delete();

        return response()->json(['message' => 'Color deleted!']);
    }
}
