<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'search' => ['nullable', 'string', 'max:100'],
            'catalog_id' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
        ]);

        $products = Product::ownedBy($request->user())->with(['category', 'catalog'])
            ->search($data['search'] ?? null)
            ->inCatalog($data['catalog_id'] ?? null)
            ->inCategory($data['category_id'] ?? null)
            ->orderBy('catalog_id')->orderBy('position')
            ->paginate($data['per_page'] ?? 15)->withQueryString();

        return ProductResource::collection($products);
    }

    public function show(Request $request, int $id): ProductResource
    {
        return new ProductResource(Product::ownedBy($request->user())->with(['category', 'catalog'])->findOrFail($id));
    }
}
