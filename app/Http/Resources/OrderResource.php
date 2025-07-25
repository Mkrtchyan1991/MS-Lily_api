<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'status'     => $this->status,
            'total'      => $this->total,
            'created_at' => $this->created_at,
            'shipping_address' => new ShippingAddressResource($this->shippingAddress),
            'items'      => OrderItemResource::collection($this->orderItems),
        ];
    }
}
