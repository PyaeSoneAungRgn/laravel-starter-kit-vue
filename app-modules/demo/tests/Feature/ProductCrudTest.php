<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Demo\Enums\ProductCategory;
use Modules\Demo\Enums\ProductStatus;
use Modules\Demo\Models\Product;

uses(RefreshDatabase::class);

test('guests are redirected to login on the products index', function () {
    $this->get('/products')->assertRedirect('/login');
});

test('authenticated users can view the products index', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/products')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('demo::products/Index', false));
});

test('authenticated users can visit the create page', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/products/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('demo::products/Create', false));
});

test('authenticated users can create a product', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->post('/products', [
        'name' => 'Wireless Headphones',
        'description' => 'Noise cancelling over-ear headphones.',
        'category' => ProductCategory::Electronics->value,
        'price' => 149.99,
        'status' => ProductStatus::Active->value,
        'is_featured' => true,
    ]);

    $response->assertRedirect('/products');
    $this->assertDatabaseHas('products', [
        'name' => 'Wireless Headphones',
        'category' => ProductCategory::Electronics->value,
        'price' => 149.99,
        'status' => ProductStatus::Active->value,
        'is_featured' => true,
    ]);
});

test('store validates the product data', function () {
    $this->actingAs(User::factory()->create());

    $this->post('/products', [])
        ->assertSessionHasErrors(['name', 'description', 'category', 'price', 'status']);
});

test('authenticated users can visit the edit page', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create();

    $this->get("/products/{$product->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('demo::products/Edit', false)->has('product'));
});

test('authenticated users can update a product', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create();

    $this->put("/products/{$product->id}", [
        'name' => 'Updated Name',
        'description' => 'Updated description.',
        'category' => ProductCategory::Clothing->value,
        'price' => 25,
        'status' => ProductStatus::Inactive->value,
        'is_featured' => false,
    ])->assertRedirect('/products');

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Name',
        'category' => ProductCategory::Clothing->value,
        'price' => 25,
        'status' => ProductStatus::Inactive->value,
        'is_featured' => false,
    ]);
});

test('update validates the product data', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create();

    $this->put("/products/{$product->id}", [])
        ->assertSessionHasErrors(['name', 'description', 'category', 'price', 'status']);
});

test('authenticated users can delete a product', function () {
    $this->actingAs(User::factory()->create());
    $product = Product::factory()->create();

    $this->delete("/products/{$product->id}")->assertRedirect('/products');

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});
