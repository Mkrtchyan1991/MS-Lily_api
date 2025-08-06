<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Color;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ColorEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_show_color_by_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $color = Color::create(['name' => 'TEST']);
        $response = $this->getJson('/api/admin/colors/TEST');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $color->id,
                'name' => 'TEST',
            ]);
    }

    public function test_admin_can_delete_color_by_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        Color::create(['name' => 'REMOVE']);
        $response = $this->deleteJson('/api/admin/colors/REMOVE');
        $response->assertStatus(200);
        $this->assertDatabaseMissing('colors', ['name' => 'REMOVE']);
    }
}
