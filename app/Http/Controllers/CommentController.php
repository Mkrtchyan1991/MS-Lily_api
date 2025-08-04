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
            'status' => 'pending'
        ]);

        return new CommentResource($comment->load(['user', 'product']));
    }

    public function index(Request $request)
    {
        $query = Comment::with(['user', 'product']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $comments = $query->get();

        return CommentResource::collection($comments);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
        ]);

        $comment = Comment::findOrFail($id);
        $comment->status = $request->status;
        $comment->save();

        return new CommentResource($comment->load(['user', 'product']));
    }

    public function indexByProduct($productId)
    {
        $comments = Comment::where('product_id', $productId)
            ->where('status', 'approved')
            ->with(['user'])->get();

        return CommentResource::collection($comments);
    }
}
