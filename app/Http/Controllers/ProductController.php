<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductImageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductImageService $images)
    {
    }

    /** Todos los productos del usuario (DataTables en el cliente aplica búsqueda, orden y paginación). */
    public function index(Request $request): View
    {
        $user = $request->user();
        $products = Product::ownedBy($user)->with(['catalog', 'category'])
            ->inCatalog($request->integer('catalog_id') ?: null)
            ->inCategory($request->integer('category_id') ?: null)
            ->orderBy('catalog_id')->orderBy('position')->get();

        return view('products.index', [
            'products' => $products,
            'catalogs' => Catalog::ownedBy($user)->orderBy('name')->get(),
            'categories' => Category::visibleTo($user)->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('products.create', $this->formData($request) + ['product' => new Product(['catalog_id' => $request->integer('catalog_id') ?: null])]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except(['photo', 'remove_photo'])->all();
        $catalog = Catalog::ownedBy($request->user())->findOrFail($data['catalog_id']);

        $data['position'] = $data['position'] ?? ((int) Product::where('catalog_id', $catalog->id)->max('position') + 1);
        if ($request->hasFile('photo')) {
            $data['image'] = $this->images->store($request->file('photo'));
        }

        $product = Product::create($data);

        return to_route('products.show', $product)->with('status', 'Producto creado correctamente.');
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        return view('products.show', ['product' => $product->load(['catalog', 'category'])]);
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorize('update', $product);

        return view('products.edit', $this->formData($request) + ['product' => $product]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = collect($request->validated())->except(['photo', 'remove_photo'])->all();
        $data['position'] = $data['position'] ?? $product->position;

        if ($request->hasFile('photo')) {
            // Reemplazo: primero se guarda la nueva, luego se borra la anterior.
            $new = $this->images->store($request->file('photo'));
            $this->images->delete($product->image);
            $data['image'] = $new;
            $data['image_url'] = null;
        } elseif ($request->boolean('remove_photo')) {
            $this->images->delete($product->image);
            $data['image'] = null;
            $data['image_url'] = null;
        }

        $product->update($data);

        return to_route('products.show', $product)->with('status', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->images->delete($product->image);
        $product->delete();

        return to_route('products.index')->with('status', 'Producto eliminado.');
    }

    /** Ordenamiento manual: sube o baja un producto dentro de su catálogo. */
    public function move(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];

        $siblings = $product->catalog->products()->get()->values();
        $index = $siblings->search(fn (Product $p) => $p->is($product));
        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index !== false && isset($siblings[$swapWith])) {
            $order = $siblings->all();
            [$order[$index], $order[$swapWith]] = [$order[$swapWith], $order[$index]];
            foreach ($order as $position => $item) {
                if ($item->position !== $position) {
                    $item->update(['position' => $position]);
                }
            }
        }

        return back()->with('status', 'Orden actualizado.');
    }

    private function formData(Request $request): array
    {
        return [
            'catalogs' => Catalog::ownedBy($request->user())->orderBy('name')->get(),
            'categories' => Category::visibleTo($request->user())->orderBy('name')->get(),
        ];
    }
}
