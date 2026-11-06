<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCatalogRequest;
use App\Http\Requests\UpdateCatalogRequest;
use App\Models\Catalog;
use App\Services\ProductImageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    public function __construct(private ProductImageService $images)
    {
    }

    public function index(Request $request): View
    {
        $catalogs = Catalog::ownedBy($request->user())
            ->withCount('products')
            ->search($request->string('search')->toString())
            ->latest()->paginate(12)->withQueryString();

        return view('catalogs.index', compact('catalogs'));
    }

    public function create(): View
    {
        return view('catalogs.create');
    }

    public function store(StoreCatalogRequest $request): RedirectResponse
    {
        $catalog = Catalog::create($request->validated() + ['user_id' => $request->user()->id]);

        return to_route('catalogs.show', $catalog)->with('status', 'Catálogo creado correctamente.');
    }

    public function show(Catalog $catalog): View
    {
        $this->authorize('view', $catalog);

        return view('catalogs.show', ['catalog' => $catalog->load('products.category')]);
    }

    public function edit(Catalog $catalog): View
    {
        $this->authorize('update', $catalog);

        return view('catalogs.edit', compact('catalog'));
    }

    public function update(UpdateCatalogRequest $request, Catalog $catalog): RedirectResponse
    {
        $this->authorize('update', $catalog);
        $catalog->update($request->validated());

        return to_route('catalogs.show', $catalog)->with('status', 'Catálogo actualizado correctamente.');
    }

    public function destroy(Catalog $catalog): RedirectResponse
    {
        $this->authorize('delete', $catalog);

        // Las filas de productos se eliminan en cascada en MySQL; los archivos se limpian explícitamente.
        $catalog->products()->get()->each(fn ($product) => $this->images->delete($product->image));
        $catalog->delete();

        return to_route('catalogs.index')->with('status', 'Catálogo eliminado.');
    }

    /** Crea una copia del catálogo (diseño y productos incluidos, con copia de las fotografías). */
    public function duplicate(Catalog $catalog): RedirectResponse
    {
        $this->authorize('view', $catalog);

        $copy = DB::transaction(function () use ($catalog) {
            $copy = $catalog->replicate();
            $copy->name = mb_substr($catalog->name, 0, 105).' (copia)';
            $copy->save();

            foreach ($catalog->products as $product) {
                $clone = $product->replicate(['external_id']);
                $clone->catalog_id = $copy->id;
                $clone->image = $this->images->copy($product->image);
                $clone->save();
            }

            return $copy;
        });

        return to_route('catalogs.show', $copy)->with('status', 'Catálogo duplicado correctamente.');
    }
}
