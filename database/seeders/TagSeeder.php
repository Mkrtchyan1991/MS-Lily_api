<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'Sport',
            'Casual',
            'Running',
            'Basketball',
            'Training',
            'Leather',
            'Winter',
            'Summer',
            'High-top',
            'Low-top',
        ];

        foreach ($tags as $tag) {
            Tag::create(['name' => $tag]);
        }
    }
}