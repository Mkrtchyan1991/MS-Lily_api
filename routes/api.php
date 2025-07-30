<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\Orders\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::post('/register', [AuthController::class, 'register']); 
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request): JsonResponse {
    $request->fulfill();
    return response()->json(['message' => 'Email verified!']);
})->middleware(['signed'])->name('verification.verify');

// Authentication disabled - removed auth:sanctum middleware
Route::group([], function () {
    Route::get('/user', fn(Request $request) => response()->json($request->user()));

    // Admin middleware disabled - removed ['admin']
    Route::group([], function () {
        Route::get('/admin/dashboard', fn(Request $request) => response()->json([
            'auth' => Auth::check(),
            'user' => $request->user(),
        ]));
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [ProfileController::class, 'profile']);
    Route::patch('/profile', [ProfileController::class, 'updateProfile']);
});

// Admin product routes - auth and admin middleware disabled
Route::prefix('admin')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products/store', [ProductController::class, 'store']);
    Route::patch('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    Route::get('/products/filter/category', [ProductController::class, 'filterByCategory']);
    Route::get('/products/filter/tag', [ProductController::class, 'filterByTag']);
    Route::get('/products/filter/brand', [ProductController::class, 'filterByBrand']);
});

// Public product routes
Route::prefix('products')->group(function () {
    Route::get('/filter/category', [ProductController::class, 'filterByCategory']);
    Route::get('/filter/tag', [ProductController::class, 'filterByTag']);
    Route::get('/filter/brand', [ProductController::class, 'filterByBrand']);

    Route::get('/allProducts', [ProductController::class, 'index']);
    Route::get('/categories', [ProductController::class, 'getCategories']);
    Route::get('/brands', [ProductController::class, 'getBrands']);
    Route::get('/tags', [ProductController::class, 'getTags']);
});

// Favorites routes - auth middleware disabled
Route::group([], function () {
    Route::post('/favorites/{productId}/toggle', [FavoriteController::class, 'toggle']);
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'remove']);
    Route::get('/favorites', [FavoriteController::class, 'getFavorites']);
});

// Orders routes - auth and role middleware disabled
Route::group([], function () {
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'userOrders']);
    Route::get('/admin/orders', [OrderController::class, 'allOrders']); // role:admin removed
    Route::patch('/admin/orders/{order}/status', [OrderController::class, 'updateStatus']); // role:admin removed
});

// Comments routes - auth and role middleware disabled
Route::group([], function () {
    Route::post('/products/{productId}/comments', [CommentController::class, 'store']);
    Route::get('/products/{productId}/comments', [CommentController::class, 'indexByProduct']);
    
    // Admin comment routes - role middleware disabled
    Route::group([], function () {
        Route::get('/admin/comments/pending', [CommentController::class, 'pending']);
        Route::patch('/admin/comments/{id}/approve', [CommentController::class, 'approve']);
    });
});