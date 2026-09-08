<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  

public function run(): void
{
    $categories = [
        ['name' => 'Decorations'],
        ['name' => 'Beads & Accessories'],
        ['name' => 'Chairs & Canopies'],
        ['name' => 'Sound & Speakers'],
        ['name' => 'Catering Equipment'],
        ['name' => 'Lighting & Stage'],
    ];

    foreach ($categories as $category) {
        Category::create($category);
    }
}

}
