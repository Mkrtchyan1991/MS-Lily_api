<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Order;
use App\Models\ShippingAddress;

class OrderUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_order_information(): void
    {
        $user = User::factory()->create();
        $address = ShippingAddress::create([
            'user_id' => $user->id,
            'full_name' => 'Old Name',
            'address_line1' => 'Old Street 1',
            'address_line2' => null,
            'city' => 'Old City',
            'state' => 'Old State',
            'postal_code' => '11111',
            'country' => 'Old Country',
            'phone' => '0000000',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'shipping_address_id' => $address->id,
            'total' => 100,
            'status' => 'pending',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/admin/orders/{$order->id}", [
            'status' => 'processing',
            'shipping_address' => [
                'city' => 'New City',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'processing')
            ->assertJsonPath('data.shipping_address.city', 'New City');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);

        $this->assertDatabaseHas('shipping_addresses', [
            'id' => $address->id,
            'city' => 'New City',
        ]);
    }
}
