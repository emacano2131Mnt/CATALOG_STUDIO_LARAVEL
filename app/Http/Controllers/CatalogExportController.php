<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Services\CatalogDesign;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Exporta un catálogo (completo o con productos seleccionados) a PDF con su diseño personalizado. */
class CatalogExportController extends Controller
{
    public function __invoke(Request $request, Catalog $catalog)
    {
        // 1) El catálogo debe pertenecer al usuario autenticado.
        $this->authorize('view', $catalog);

        // 2) Cada producto seleccionado debe pertenecer a ESTE catálogo.
        $data = $request->validate([
            'products' => ['sometimes', 'array', 'min:1', 'max:500'],
            'products.*' => ['integer', Rule::exists('products', 'id')->where('catalog_id', $catalog->id)],
            'download' => ['sometimes', 'boolean'],
        ], [
            'products.*.exists' => 'Alguno de los productos seleccionados no pertenece a este catálogo.',
        ], ['products' => 'productos']);

        $products = $catalog->products()->with('category')
            ->when($data['products'] ?? null, fn ($q, $ids) => $q->whereIn('id', $ids))
            ->get();

        $pdf = Pdf::loadView('pdf.catalog', [
            'catalog' => $catalog,
            'design' => CatalogDesign::resolve($catalog),
            'products' => $products,
            'pdf' => true,
        ])->setPaper('a4');

        $filename = Str::slug($catalog->name) ?: 'catalogo';
        $filename .= '.pdf';

        return $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
    }
}
