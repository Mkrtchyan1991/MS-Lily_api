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
            'id'          => $this->id,  //Produkt id
            'name'        => $this->name,  //Produkt name
            'description' => $this->description,  //Produkt Beschreibung
            'category'    => [
                //Gipt Kategorie id zurück,wenn gips nicht dann null
                'id'   => $this->category->id ?? null,
                 //Gipt Kategorie name zurück,wenn gips nicht dann null
                'name' => $this->category->name ?? null,
            ],
            'brand'       => [
                 //Gipt brand id zurück,wenn gips nicht dann null
                'id'   => $this->brand->id ?? null,
                 //Gipt brand name zurück,wenn gips nicht dann null
                'name' => $this->brand->name ?? null,
            ],
             //Gipt die Daten zurück
            'tags'        => $this->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            }),
            'color'       => $this->color,
            'size'        => $this->size,
            'price'       => $this->price ? (float) $this->price : null,
            'stock'       => $this->stock ?? 0,
            'in_stock'    => ($this->stock ?? 0) > 0,
             //Wenn gips bild bring zurück storage/ URL,wenn gips nicht dann null
            'image'       => $this->image ? asset('storage/' . $this->image) : null,
            //Gipt zurück die Erstellungsdatum
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}