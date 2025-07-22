<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Nike',
            'Adidas',
            'Puma',
            'Reebok',
            'New Balance',
            'Under Armour',
            'Converse',
            'Vans',
            'Asics',
            'Fila',
        ];

        foreach ($brands as $brand) {
            Brand::insert(['name' => $brand]);
        }
    }
}