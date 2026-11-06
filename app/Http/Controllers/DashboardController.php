<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'catalogs' => Catalog::ownedBy($user)->count(),
            'products' => Product::ownedBy($user)->count(),
            'categories' => Category::visibleTo($user)->count(),
        ];

        // Distribución de productos propios por categoría (datos reales vía Eloquent).
        $byCategory = Category::visibleTo($user)
            ->withCount(['products' => fn (Builder $q) => $q->ownedBy($user)])
            ->get()->filter(fn ($c) => $c->products_count > 0)->sortByDesc('products_count');

        $labels = $byCategory->pluck('name')->values()->all();
        $values = $byCategory->pluck('products_count')->values()->all();

        $uncategorized = Product::ownedBy($user)->whereNull('category_id')->count();
        if ($uncategorized > 0) {
            $labels[] = 'Sin categoría';
            $values[] = $uncategorized;
        }

        return view('dashboard.index', [
            'user' => $user,
            'stats' => $stats,
            'chart' => ['labels' => $labels, 'values' => $values],
            'recentProducts' => Product::ownedBy($user)->with(['catalog', 'category'])->latest()->limit(5)->get(),
            'recentCatalogs' => Catalog::ownedBy($user)->withCount('products')->latest()->limit(5)->get(),
        ]);
    }
}
