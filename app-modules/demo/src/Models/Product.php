<?php

namespace Modules\Demo\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Demo\Enums\ProductCategory;
use Modules\Demo\Enums\ProductStatus;

#[Fillable([
    'name',
    'description',
    'category',
    'price',
    'status',
    'is_featured',
])]
class Product extends Model
{
    use HasFactory;

    protected $casts = [
        'category' => ProductCategory::class,
        'status' => ProductStatus::class,
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
    ];
}
