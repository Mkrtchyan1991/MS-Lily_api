<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_retrieve_favorite_products(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Test Category']);
        $brand = Brand::create(['name' => 'Test Brand']);
        $product = Product::create([
            'name' => 'Sample Product',
            'description' => 'Desc',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 10,
            'stock' => 5,
        ]);

        $user->favoriteProducts()->attach($product->id);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/favorites');

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $product->id]);
    }
}
