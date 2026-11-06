<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Catalog;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with(['catalog', 'category'])->search($request->string('search')->toString())
            ->inCatalog($request->integer('catalog_id') ?: null)
            ->inCategory($request->integer('category_id') ?: null)->latest()->paginate(15)->withQueryString();

        return view('products.index', ['products' => $products]);
    }

    public function create(): View
    {
        return view('products.create', ['catalogs' => Catalog::orderBy('name')->get(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->validated());
        return to_route('products.show', $product)->with('status', 'Producto creado.');
    }

    public function show(Product $product): View
    {
        return view('products.show', ['product' => $product->load(['catalog', 'category'])]);
    }

    public function edit(Product $product): View
    {
        return view('products.edit', ['product' => $product, 'catalogs' => Catalog::orderBy('name')->get(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());
        return to_route('products.show', $product)->with('status', 'Producto actualizado.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return to_route('products.index')->with('status', 'Producto eliminado.');
    }
}
