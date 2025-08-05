<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of tags
     */
    public function index()
    {
        return response()->json(Tag::all());
    }

    /**
     * Display the specified tag
     */
    public function show($id)
    {
        $tag = Tag::findOrFail($id);
        return response()->json($tag);
    }

    /**
     * Store a newly created tag
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags',
        ]);

        $tag = Tag::create([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Tag created!', 'tag' => $tag], 201);
    }

    /**
     * Update the specified tag
     */
    public function update(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
        ]);

        $tag->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Tag updated!', 'tag' => $tag]);
    }

    /**
     * Remove the specified tag
     */
    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();

        return response()->json(['message' => 'Tag deleted!']);
    }
}
