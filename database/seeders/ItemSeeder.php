<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   

public function run(): void
{
    Item::create([
        'user_id' => 1,
        'category_id' => 1,
        'title' => 'Wedding Decoration Set',
        'description' => 'Complete wedding decoration materials including flowers and backdrop.',
        'price_per_day' => 50000,
        'quantity' => 1,
        'location' => 'Lagos',
        'status' => 'available',
    ]);

    Item::create([
        'user_id' => 1,
        'category_id' => 4,
        'title' => 'Sound System & Speakers',
        'description' => 'High quality speakers with mixer and microphones.',
        'price_per_day' => 70000,
        'quantity' => 1,
        'location' => 'Ibadan',
        'status' => 'available',
    ]);
}

}
