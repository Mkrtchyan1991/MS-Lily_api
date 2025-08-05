<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Http\Resources\CommentResource;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    /**
     * Store a new comment for a product
     */
    public function store(Request $request, $productId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $status = auth()->user()?->role === 'admin'
            ? Comment::STATUS_APPROVED
            : Comment::STATUS_PENDING;

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'product_id' => $productId,
            'content' => $request->content,
            'status' => $status,
        ]);

        return response()->json([
            'data' => new CommentResource($comment->load(['user', 'product'])),
            'message' => 'Comment created successfully'
        ], 201);
    }

    /**
     * Update an existing comment (only owner)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = Comment::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $status = auth()->user()?->role === 'admin'
            ? Comment::STATUS_APPROVED
            : Comment::STATUS_PENDING;

        $comment->update([
            'content' => $request->content,
            'status' => $status,
        ]);

        return response()->json([
            'data' => new CommentResource($comment->load(['user', 'product'])),
            'message' => 'Comment updated successfully'
        ]);
    }

    /**
     * Get comments for a specific product (public endpoint)
     */
    public function indexByProduct(Request $request, $productId)
    {
        $query = Comment::where('product_id', $productId)
            ->where('status', 'approved')
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        // Add pagination support
        $perPage = $request->get('per_page', 15);
        $comments = $query->paginate($perPage);

        return response()->json([
            'data' => CommentResource::collection($comments->items()),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
                'last_page' => $comments->lastPage(),
            ]
        ]);
    }

    /**
     * Get all comments with filtering and pagination (Admin only)
     */
    public function getAllComments(Request $request)
    {
        $query = Comment::with(['user', 'product']);

        // Status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = ['created_at', 'updated_at', 'id', 'status'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $comments = $query->paginate($perPage);

        return response()->json([
            'data' => CommentResource::collection($comments->items()),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
                'last_page' => $comments->lastPage(),
            ]
        ]);
    }

    /**
     * Get a specific comment (Admin only)
     */
    public function getComment($id)
    {
        $comment = Comment::with(['user', 'product'])->findOrFail($id);

        return response()->json([
            'data' => new CommentResource($comment)
        ]);
    }

    /**
     * Approve a comment (Admin only)
     */
    public function approve($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->status = 'approved';
        $comment->save();

        return response()->json([
            'data' => new CommentResource($comment->load(['user', 'product'])),
            'message' => 'Comment approved successfully'
        ]);
    }

    /**
     * Reject a comment (Admin only)
     */
    public function reject($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->status = 'rejected';
        $comment->save();

        return response()->json([
            'data' => new CommentResource($comment->load(['user', 'product'])),
            'message' => 'Comment rejected successfully'
        ]);
    }

    /**
     * Delete a comment (Admin only)
     */
    public function deleteComment($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();

        return response()->json([
            'data' => null,
            'message' => 'Comment deleted successfully'
        ]);
    }

    /**
     * Batch update comments (Admin only)
     */
    public function batchUpdateComments(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:comments,id',
            'action' => 'required|string|in:approve,reject,delete'
        ]);

        $ids = $request->ids;
        $action = $request->action;
        $updated = 0;

        switch ($action) {
            case 'approve':
                $updated = Comment::whereIn('id', $ids)->update(['status' => 'approved']);
                break;

            case 'reject':
                $updated = Comment::whereIn('id', $ids)->update(['status' => 'rejected']);
                break;

            case 'delete':
                $updated = Comment::whereIn('id', $ids)->delete();
                break;
        }

        return response()->json([
            'data' => null,
            'message' => "Successfully {$action}d {$updated} comment(s)"
        ]);
    }

    /**
     * Update comment status (Admin only)
     * Handles PATCH /admin/comments/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected'
        ]);

        $comment = Comment::findOrFail($id);
        $comment->status = $request->status;
        $comment->save();

        return response()->json([
            'data' => new CommentResource($comment->load(['user', 'product'])),
            'message' => "Comment status updated to {$request->status} successfully"
        ]);
    }
}