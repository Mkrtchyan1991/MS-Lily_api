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
use App\Http\Controllers\Admin\UserController;

use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request): JsonResponse {
    $request->fulfill();
    return response()->json(['message' => 'Email verified!']);
})->middleware(['signed'])->name('verification.verify');

// Protected routes (require Bearer token authentication)
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/user', fn(Request $request) => response()->json($request->user()));
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all-devices', [AuthController::class, 'logoutAllDevices']);

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'profile']);
    Route::patch('/profile', [ProfileController::class, 'updateProfile']);

    // Admin routes
    Route::group(['middleware' => ['admin']], function () {
        Route::get('/admin/dashboard', fn(Request $request) => response()->json([
            'auth' => Auth::check(),
            'user' => $request->user(),
        ]));
    });
});

// Admin product routes (protected)
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products/store', [ProductController::class, 'store']);
    Route::patch('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});

// Public product routes (no authentication required)
Route::prefix('products')->group(function () {
    // Main products endpoint with filtering capabilities
    Route::get('/allProducts', [ProductController::class, 'index']);

    // Combined filter options endpoint (optional - provides all filter data in one request)
    Route::get('/filter-options', [ProductController::class, 'getFilterOptions']);

    // Individual show route
    Route::get('/{id}', [ProductController::class, 'show']);
});

// Favorites routes (protected)
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/favorites/{productId}/toggle', [FavoriteController::class, 'toggle']);
    Route::delete('/favorites/{productId}', [FavoriteController::class, 'remove']);
    Route::get('/favorites', [FavoriteController::class, 'getFavorites']);
});

// Orders routes (protected)
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'userOrders']);

    // Admin order routes
    Route::group(['middleware' => ['admin']], function () {
        Route::get('/admin/orders', [OrderController::class, 'allOrders']);
        Route::patch('/admin/orders/{order}/status', [OrderController::class, 'updateStatus']);
    });
});

// Comments routes (protected)
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/products/{productId}/comments', [CommentController::class, 'store']);
    Route::get('/products/{productId}/comments', [CommentController::class, 'indexByProduct']);

    // Admin comment routes
    Route::group(['middleware' => ['admin']], function () {
        Route::get('/admin/comments', [CommentController::class, 'index']);
        Route::patch('/admin/comments/{id}/status', [CommentController::class, 'updateStatus']);
    });
});

// Admin user management routes (protected by auth:sanctum and admin middleware)
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // User management routes
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::patch('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});