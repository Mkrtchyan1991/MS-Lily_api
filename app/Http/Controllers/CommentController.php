<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Http\Resources\CommentResource;

class CommentController extends Controller
{
    public function store(Request $request, $productId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'product_id' => $productId,
            'content' => $request->content,
            'approved' => false
        ]);

        return new CommentResource($comment->load(['user', 'product']));
    }

    public function pending()
    {
        $comments = Comment::where('approved', false)->with(['user', 'product'])->get();
        return CommentResource::collection($comments);
    }

    public function approve($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->approved = true;
        $comment->save();

        return new CommentResource($comment->load(['user', 'product']));
    }

    public function indexByProduct($productId)
    {
        $comments = Comment::where('product_id', $productId)
            ->where('approved', true)
            ->with(['user'])->get();

        return CommentResource::collection($comments);
    }
}
