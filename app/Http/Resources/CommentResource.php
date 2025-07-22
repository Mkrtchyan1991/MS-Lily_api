<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'content'   => $this->content,
            'approved'  => $this->approved,
            'user'      => new UserResource($this->whenLoaded('user')),
            'product_id'=> $this->product_id,
            'created_at'=> $this->created_at->toDateTimeString(),
        ];
    }
}
