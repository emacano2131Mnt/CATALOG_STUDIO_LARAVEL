<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('reports.index', [
            'catalogs' => Catalog::ownedBy($user)->withCount('products')->orderBy('name')->get(),
            'categories' => Category::visibleTo($user)->orderBy('name')->get(),
        ]);
    }

    /** Informe tabular PDF de productos del usuario, filtrable por catálogo y categoría. */
    public function products(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'catalog_id' => ['nullable', 'integer', Rule::exists('catalogs', 'id')->where('user_id', $user->id)],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where(fn ($q) => $q->where('user_id', $user->id)->orWhereNull('user_id'))],
            'download' => ['sometimes', 'boolean'],
        ]);

        $products = Product::ownedBy($user)->with(['catalog', 'category'])
            ->inCatalog($data['catalog_id'] ?? null)
            ->inCategory($data['category_id'] ?? null)
            ->orderBy('catalog_id')->orderBy('position')->get();

        $pdf = Pdf::loadView('pdf.products-report', [
            'products' => $products,
            'user' => $user,
            'generatedAt' => now(),
            'total' => $products->sum('price'),
        ])->setPaper('a4');

        return $request->boolean('download') ? $pdf->download('informe-productos.pdf') : $pdf->stream('informe-productos.pdf');
    }
}
