<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|array',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $addressData = $request->shipping_address;
            $shipping = ShippingAddress::create(array_merge($addressData, [
                'user_id' => $request->user()->id
            ]));

            $total = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $total += $item['quantity'] * $product->price;
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'shipping_address_id' => $shipping->id,
                'total' => $total,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'total' => $item['quantity'] * $product->price,
                ]);
            }

            DB::commit();

            return new OrderResource($order->load(['shippingAddress', 'orderItems.product']));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to place order', 'details' => $e->getMessage()], 500);
        }
    }

    public function userOrders(Request $request)
    {
        $orders = $request->user()->orders()->with(['shippingAddress', 'orderItems.product'])->latest()->get();
        return OrderResource::collection($orders);
    }

    public function allOrders()
    {
        $orders = Order::with(['shippingAddress', 'orderItems.product', 'user'])->latest()->get();
        return OrderResource::collection($orders);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,canceled' //targmanel
        ]);

        $order->status = $request->status;
        $order->save();

        return new OrderResource($order->fresh(['shippingAddress', 'orderItems.product']));
    }
}