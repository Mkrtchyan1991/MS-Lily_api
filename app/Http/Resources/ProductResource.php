<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Gibt die Produktdaten als Array zurück
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? null,
            ],
            'brand' => [
                'id' => $this->brand->id ?? null,
                'name' => $this->brand->name ?? null,
            ],
            'tags' => $this->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            }),
            'color' => $this->color,
            'size' => $this->size,
            'price' => $this->price ? (float) $this->price : null,
            'stock' => $this->stock ?? 0,
            'in_stock' => ($this->stock ?? 0) > 0,

            // Generate absolute URL for image
            'image' => $this->image ? $this->getAbsoluteImageUrl() : null,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'textDark' => $this->textDark,
            'del' => $this->del,
            'textSuccess' => $this->textSuccess,
            'star' => $this->star,
        ];
    }

    /**
     * Generate absolute URL for the image
     *
     * @return string
     */
    private function getAbsoluteImageUrl(): string
    {
        // Get the base URL from config or request
        $baseUrl = config('app.url') ?: request()->getSchemeAndHttpHost();

        // Remove trailing slash from base URL and leading slash from image path
        $baseUrl = rtrim($baseUrl, '/');
        $imagePath = ltrim($this->image, '/');

        // Construct full URL
        return $baseUrl . '/storage/' . $imagePath;
    }
}
