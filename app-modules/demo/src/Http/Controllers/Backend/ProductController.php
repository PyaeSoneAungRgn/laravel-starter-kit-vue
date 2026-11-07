<?php

namespace Modules\Demo\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Demo\Enums\ProductCategory;
use Modules\Demo\Enums\ProductStatus;
use Modules\Demo\Models\Product;

class ProductController extends Controller
{
    public function __construct(
        public readonly Product $model,
    ) {}

    public function index(): Response
    {
        Gate::authorize('viewAny', Product::class);

        return Inertia::render('demo::products/Index', [
            'products' => $this->model
                ->query()
                ->latest()
                ->paginate(),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Product::class);

        return Inertia::render('demo::products/Create', [
            'categories' => ProductCategory::cases(),
            'statuses' => ProductStatus::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Product::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', Rule::enum(ProductCategory::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'is_featured' => ['sometimes', 'boolean'],
        ]);

        $this->model->create($validated);

        return to_route('products.index');
    }

    public function edit(Product $product): Response
    {
        Gate::authorize('update', $product);

        return Inertia::render('demo::products/Edit', [
            'product' => $product,
            'categories' => ProductCategory::cases(),
            'statuses' => ProductStatus::cases(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', Rule::enum(ProductCategory::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'is_featured' => ['sometimes', 'boolean'],
        ]);

        $product->update($validated);

        return to_route('products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        $product->delete();

        return to_route('products.index');
    }
}
