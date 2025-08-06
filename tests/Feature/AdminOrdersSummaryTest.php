<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Order;
use App\Models\ShippingAddress;

class AdminOrdersSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_orders_endpoint_returns_summary(): void
    {
        $user = User::factory()->create();
        $address = ShippingAddress::create([
            'user_id' => $user->id,
            'full_name' => 'John Doe',
            'address_line1' => '123 Street',
            'address_line2' => null,
            'city' => 'City',
            'state' => 'State',
            'postal_code' => '12345',
            'country' => 'Country',
            'phone' => '1234567890',
        ]);

        $orders = [
            ['status' => 'pending', 'total' => 100],
            ['status' => 'pending', 'total' => 150],
            ['status' => 'processing', 'total' => 200],
            ['status' => 'shipped', 'total' => 250],
            ['status' => 'delivered', 'total' => 300],
            ['status' => 'canceled', 'total' => 50],
        ];

        foreach ($orders as $data) {
            Order::create([
                'user_id' => $user->id,
                'shipping_address_id' => $address->id,
                'total' => $data['total'],
                'status' => $data['status'],
            ]);
        }

        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/orders');

        $response->assertStatus(200)
            ->assertJsonPath('summary.total_orders', 6)
            ->assertJsonPath('summary.pending', 2)
            ->assertJsonPath('summary.processing', 1)
            ->assertJsonPath('summary.shipped', 1)
            ->assertJsonPath('summary.delivered', 1)
            ->assertJsonPath('summary.canceled', 1)
            ->assertJsonPath('summary.total_revenue', 1050);
    }
}
