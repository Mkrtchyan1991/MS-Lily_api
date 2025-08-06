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
        $orders = $request->user()
            ->orders()
            ->with(['shippingAddress', 'orderItems.product'])
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function allOrders()
    {
        $orders = Order::with(['shippingAddress', 'orderItems.product', 'user'])->latest()->get();

        $summary = [
            'total_orders' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'canceled' => Order::where('status', 'canceled')->count(),
            'total_revenue' => Order::sum('total'),
        ];

        return OrderResource::collection($orders)->additional([
            'summary' => $summary,
        ]);
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

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'sometimes|in:pending,processing,shipped,delivered,canceled',
            'shipping_address' => 'sometimes|array',
            'shipping_address.full_name' => 'sometimes|string',
            'shipping_address.address_line1' => 'sometimes|string',
            'shipping_address.address_line2' => 'nullable|string',
            'shipping_address.city' => 'sometimes|string',
            'shipping_address.state' => 'sometimes|string',
            'shipping_address.postal_code' => 'sometimes|string',
            'shipping_address.country' => 'sometimes|string',
            'shipping_address.phone' => 'sometimes|string',
        ]);

        DB::beginTransaction();

        try {
            if ($request->filled('status')) {
                $order->status = $request->status;
            }

            if ($request->has('shipping_address')) {
                $order->shippingAddress->update($request->shipping_address);
            }

            $order->save();

            DB::commit();

            return new OrderResource($order->fresh(['shippingAddress', 'orderItems.product']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update order', 'details' => $e->getMessage()], 500);
        }
    }
}
