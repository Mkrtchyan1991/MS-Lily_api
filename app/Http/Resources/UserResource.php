<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'country'  => $this->country,
            'city'  => $this->city,
            'postal_code'  => $this->postal_code,
            'address' => $this->address,
            'email_verified_at' => $this->email_verified_at,
            'is_verified' => !is_null($this->email_verified_at),
            'role'  => $this ->role,                                    
            'last_name'=>$this->last_name,                                           
            'mobile_number'=>$this->mobile_number
        ];
    }
}
