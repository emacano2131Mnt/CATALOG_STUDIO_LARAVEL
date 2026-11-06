<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CatalogResource;
use App\Http\Resources\ProductResource;
use App\Models\Catalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * API REST de catálogos. Todas las consultas se restringen al usuario autenticado;
 * un catálogo ajeno responde 404 (no se revela su existencia).
 */
class CatalogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $catalogs = Catalog::ownedBy($request->user())->withCount('products')
            ->search($data['search'] ?? null)->latest()->paginate($data['per_page'] ?? 15)->withQueryString();

        return CatalogResource::collection($catalogs);
    }

    public function show(Request $request, int $id): CatalogResource
    {
        $catalog = Catalog::ownedBy($request->user())->withCount('products')->findOrFail($id);

        return new CatalogResource($catalog);
    }

    public function products(Request $request, int $id): AnonymousResourceCollection
    {
        $catalog = Catalog::ownedBy($request->user())->findOrFail($id);
        $data = $request->validate(['per_page' => ['nullable', 'integer', 'between:1,100']]);

        return ProductResource::collection(
            $catalog->products()->with('category')->paginate($data['per_page'] ?? 15)->withQueryString()
        );
    }
}
