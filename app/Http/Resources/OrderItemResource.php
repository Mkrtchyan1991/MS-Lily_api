<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'product_name' => $this->product?->name,
            'quantity'   => $this->quantity, //Produkt zahl
            'price'      => $this->price,
            'total'      => $this->total, // Verkaufte Produkt zahl
        ];
    }
}
