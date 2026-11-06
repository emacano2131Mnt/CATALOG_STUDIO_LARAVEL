<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogDesignRequest;
use App\Models\Catalog;
use App\Services\CatalogDesign;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DesignerController extends Controller
{
    /** Lista de catálogos propios para elegir cuál diseñar. */
    public function index(Request $request): View
    {
        $catalogs = Catalog::ownedBy($request->user())->withCount('products')->orderBy('name')->get();

        return view('designer.index', compact('catalogs'));
    }

    public function edit(Catalog $catalog): View
    {
        $this->authorize('update', $catalog);

        return view('designer.edit', [
            'catalog' => $catalog,
            'design' => CatalogDesign::resolve($catalog),
            'templates' => CatalogDesign::templates(),
            'fonts' => CatalogDesign::FONTS,
        ]);
    }

    public function update(CatalogDesignRequest $request, Catalog $catalog): RedirectResponse
    {
        $this->authorize('update', $catalog);
        $catalog->update(['design' => $request->design()]);

        return to_route('designer.edit', $catalog)->with('status', 'Diseño guardado correctamente.');
    }

    /** Vista previa HTML (sin guardar): usa el mismo Blade que el PDF. */
    public function preview(CatalogDesignRequest $request, Catalog $catalog): View
    {
        $this->authorize('view', $catalog);
        $override = $request->has('design') ? $request->design() : null;

        return view('pdf.catalog', [
            'catalog' => $catalog,
            'design' => CatalogDesign::resolve($catalog, $override),
            'products' => $catalog->products()->with('category')->limit(24)->get(),
            'pdf' => false,
        ]);
    }
}
