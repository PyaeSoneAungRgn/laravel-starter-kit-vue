<?php

namespace Modules\Demo\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Demo\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()->count(20)->create();
    }
}
