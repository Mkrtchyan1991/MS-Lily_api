<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Comment;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(): Product
    {
        $category = Category::create(['name' => 'Test Category']);
        $brand = Brand::create(['name' => 'Test Brand']);

        return Product::create([
            'name' => 'Sample Product',
            'description' => 'Desc',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 10,
            'stock' => 5,
        ]);
    }

    public function test_admin_comment_is_approved_automatically(): void
    {
        $product = $this->createProduct();
        $admin = User::factory()->create(['role' => 'admin']);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/products/{$product->id}/comments", [
            'content' => 'Admin comment',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.status', Comment::STATUS_APPROVED);

        $this->assertDatabaseHas('comments', [
            'content' => 'Admin comment',
            'status' => Comment::STATUS_APPROVED,
        ]);
    }

    public function test_regular_user_comment_is_pending(): void
    {
        $product = $this->createProduct();
        $user = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/products/{$product->id}/comments", [
            'content' => 'User comment',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.status', Comment::STATUS_PENDING);

        $this->assertDatabaseHas('comments', [
            'content' => 'User comment',
            'status' => Comment::STATUS_PENDING,
        ]);
    }

    public function test_user_can_update_their_comment(): void
    {
        $product = $this->createProduct();
        $user = User::factory()->create(['role' => 'user']);

        $comment = Comment::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'content' => 'Original comment',
            'status' => Comment::STATUS_PENDING,
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/comments/{$comment->id}", [
            'content' => 'Updated comment',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.content', 'Updated comment')
                 ->assertJsonPath('data.status', Comment::STATUS_PENDING);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated comment',
        ]);
    }

    public function test_user_cannot_update_others_comment(): void
    {
        $product = $this->createProduct();
        $owner = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);

        $comment = Comment::create([
            'user_id' => $owner->id,
            'product_id' => $product->id,
            'content' => 'Owner comment',
            'status' => Comment::STATUS_PENDING,
        ]);

        Sanctum::actingAs($other);

        $response = $this->patchJson("/api/comments/{$comment->id}", [
            'content' => 'Hacked comment',
        ]);

        $response->assertStatus(404);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Owner comment',
        ]);
    }
}

