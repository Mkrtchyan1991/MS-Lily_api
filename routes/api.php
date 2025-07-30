<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\Orders\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommentController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Authentication routes with rate limiting
Route::middleware(['throttle:auth'])->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
});

// Email verification
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['message' => 'Email verified successfully!']);
})->middleware(['signed'])->name('verification.verify');

// Resend verification email
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent!']);
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| Public Product Routes
|--------------------------------------------------------------------------
*/

Route::prefix('products')->group(function () {
    // Product listings and details
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    Route::get('/{product}', [ProductController::class, 'show'])->name('products.show');
    
    // Product filtering
    Route::get('/filter/category', [ProductController::class, 'filterByCategory'])->name('products.filter.category');
    Route::get('/filter/tag', [ProductController::class, 'filterByTag'])->name('products.filter.tag');
    Route::get('/filter/brand', [ProductController::class, 'filterByBrand'])->name('products.filter.brand');
    Route::get('/search', [ProductController::class, 'search'])->name('products.search');
    
    // Product metadata
    Route::get('/meta/categories', [ProductController::class, 'getCategories'])->name('products.categories');
    Route::get('/meta/brands', [ProductController::class, 'getBrands'])->name('products.brands');
    Route::get('/meta/tags', [ProductController::class, 'getTags'])->name('products.tags');
    
    // Public comments (read-only)
    Route::get('/{product}/comments', [CommentController::class, 'indexByProduct'])->name('comments.product');
});

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // User info and logout
    Route::get('/user', fn(Request $request) => response()->json($request->user()));
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Profile management
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // Favorites management
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/{product}/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
        Route::delete('/{product}', [FavoriteController::class, 'remove'])->name('favorites.remove');
    });

    // Order management
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });

    // Comment management (verified users only)
    Route::middleware(['verified'])->group(function () {
        Route::post('/products/{product}/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Admin-Only Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth:sanctum', 'verified', 'role:admin'])->group(function () {
    // Admin dashboard
    Route::get('/dashboard', [AdminProductController::class, 'dashboard'])->name('admin.dashboard');
    
    // Product management
    Route::prefix('products')->group(function () {
        Route::get('/', [AdminProductController::class, 'index'])->name('admin.products.index');
        Route::post('/', [AdminProductController::class, 'store'])->name('admin.products.store');
        Route::get('/{product}', [AdminProductController::class, 'show'])->name('admin.products.show');
        Route::patch('/{product}', [AdminProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/{product}', [AdminProductController::class, 'destroy'])->name('admin.products.destroy');
        
        // Admin-specific product filtering (includes unpublished products)
        Route::get('/filter/category', [AdminProductController::class, 'filterByCategory'])->name('admin.products.filter.category');
        Route::get('/filter/tag', [AdminProductController::class, 'filterByTag'])->name('admin.products.filter.tag');
        Route::get('/filter/brand', [AdminProductController::class, 'filterByBrand'])->name('admin.products.filter.brand');
        Route::get('/filter/status', [AdminProductController::class, 'filterByStatus'])->name('admin.products.filter.status');
        
        // Bulk operations
        Route::patch('/bulk/status', [AdminProductController::class, 'bulkUpdateStatus'])->name('admin.products.bulk.status');
        Route::delete('/bulk/delete', [AdminProductController::class, 'bulkDelete'])->name('admin.products.bulk.delete');
    });

    // Order management
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'adminIndex'])->name('admin.orders.index');
        Route::get('/{order}', [OrderController::class, 'adminShow'])->name('admin.orders.show');
        Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
        Route::get('/filter/status', [OrderController::class, 'filterByStatus'])->name('admin.orders.filter.status');
    });

    // Comment moderation
    Route::prefix('comments')->group(function () {
        Route::get('/pending', [CommentController::class, 'pending'])->name('admin.comments.pending');
        Route::get('/all', [CommentController::class, 'adminIndex'])->name('admin.comments.index');
        Route::patch('/{comment}/approve', [CommentController::class, 'approve'])->name('admin.comments.approve');
        Route::patch('/{comment}/reject', [CommentController::class, 'reject'])->name('admin.comments.reject');
        Route::delete('/{comment}', [CommentController::class, 'adminDestroy'])->name('admin.comments.destroy');
    });

    // User management
    Route::prefix('users')->group(function () {
        Route::get('/', [ProfileController::class, 'adminIndex'])->name('admin.users.index');
        Route::get('/{user}', [ProfileController::class, 'adminShow'])->name('admin.users.show');
        Route::patch('/{user}/status', [ProfileController::class, 'updateStatus'])->name('admin.users.update-status');
    });

    // Analytics and reports
    Route::prefix('analytics')->group(function () {
        Route::get('/overview', [AdminProductController::class, 'analyticsOverview'])->name('admin.analytics.overview');
        Route::get('/products', [AdminProductController::class, 'productAnalytics'])->name('admin.analytics.products');
        Route::get('/orders', [OrderController::class, 'orderAnalytics'])->name('admin.analytics.orders');
    });
});

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found.'
    ], 404);
});