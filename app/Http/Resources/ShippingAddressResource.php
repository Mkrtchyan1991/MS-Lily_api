<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'full_name'      => $this->full_name,
            'address_line1'  => $this->address_line1,
            'address_line2'  => $this->address_line2,
            'city'           => $this->city,
            'state'          => $this->state,
            'postal_code'    => $this->postal_code,
            'country'        => $this->country,
            'phone'          => $this->phone,
        ];
    }
}
