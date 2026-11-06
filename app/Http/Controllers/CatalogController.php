<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCatalogRequest;
use App\Http\Requests\UpdateCatalogRequest;
use App\Models\Catalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $catalogs = Catalog::withCount('products')->search($request->string('search')->toString())->latest()->paginate(12)->withQueryString();
        return view('catalogs.index', compact('catalogs'));
    }

    public function create(): View
    {
        return view('catalogs.create');
    }

    public function store(StoreCatalogRequest $request): RedirectResponse
    {
        $catalog = Catalog::create($request->validated() + ['user_id' => $request->user()->id]);
        return to_route('catalogs.show', $catalog)->with('status', 'Catálogo creado.');
    }

    public function show(Catalog $catalog): View
    {
        return view('catalogs.show', ['catalog' => $catalog->load('products.category')]);
    }

    public function edit(Catalog $catalog): View
    {
        return view('catalogs.edit', compact('catalog'));
    }

    public function update(UpdateCatalogRequest $request, Catalog $catalog): RedirectResponse
    {
        $catalog->update($request->validated());
        return to_route('catalogs.show', $catalog)->with('status', 'Catálogo actualizado.');
    }

    public function destroy(Catalog $catalog): RedirectResponse
    {
        $catalog->delete();
        return to_route('catalogs.index')->with('status', 'Catálogo eliminado.');
    }
}
