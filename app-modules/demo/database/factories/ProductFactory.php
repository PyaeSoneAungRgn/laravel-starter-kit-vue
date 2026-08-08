<?php

namespace Modules\Demo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Demo\Enums\ProductCategory;
use Modules\Demo\Enums\ProductStatus;
use Modules\Demo\Models\Product;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(ProductCategory::cases()),
            'price' => fake()->randomFloat(2, 1, 1000),
            'status' => fake()->randomElement(ProductStatus::cases()),
            'is_featured' => fake()->boolean(),
        ];
    }
}
