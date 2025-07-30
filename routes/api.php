<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
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


Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('>json(['message' => 'Email verified!'])');
})->middleware(['signed'])->name('verification.verify');


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', fn(Request $request) => response()->json($request->user()));

    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/dashboard', fn(Request $request) => response()->json([
            'auth' => Auth::check(),
            'user' => $request->user(),
        ]));
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [ProfileController::class, 'profile']);
    Route::patch('/profile', [ProfileController::class, 'updateProfile']);
});

 Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products/store', [ProductController::class, 'store']);
    Route::patch('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    Route::get('/products/filter/category', [ProductController::class, 'filterByCategory']);
    Route::get('/products/filter/tag', [ProductController::class, 'filterByTag']);
    Route::get('/products/filter/brand', [ProductController::class, 'filterByBrand']);
 });

Route::prefix('products')->group(function () {
    Route::get('/filter/category', [ProductController::class, 'filterByCategory']);
    Route::get('/filter/tag', [ProductController::class, 'filterByTag']);
    Route::get('/filter/brand', [ProductController::class, 'filterByBrand']);

    Route::get('/allProducts', [ProductController::class, 'index']);
    Route::get('/categories', [ProductController::class, 'getCategories']);
    Route::get('/brands', [ProductController::class, 'getBrands']);
    Route::get('/tags', [ProductController::class, 'getTags']);
});

 Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/favorites/{productId}/toggle', [FavoriteController::class, 'toggle']);
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'remove']);
    Route::get('/favorites', [FavoriteController::class, 'getFavorites']);
 });

 Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);                 // Place new order
    Route::get('/orders', [OrderController::class, 'userOrders']);             // User's own orders
    Route::get('/admin/orders', [OrderController::class, 'allOrders'])->middleware('role:admin'); // Admin only
    Route::patch('/admin/orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware('role:admin');
 });

 Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/products/{productId}/comments', [CommentController::class, 'store']);
    Route::get('/products/{productId}/comments', [CommentController::class, 'indexByProduct']);
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/comments/pending', [CommentController::class, 'pending']);
        Route::patch('/admin/comments/{id}/approve', [CommentController::class, 'approve']);
    });
  });
  
