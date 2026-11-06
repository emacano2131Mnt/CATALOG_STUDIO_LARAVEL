<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /** Categorías propias y globales; el conteo incluye solo productos del usuario. */
    public function index(Request $request): View
    {
        $user = $request->user();
        $categories = Category::visibleTo($user)
            ->withCount(['products' => fn (Builder $q) => $q->ownedBy($user)])
            ->orderBy('name')->get();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated() + ['user_id' => $request->user()->id]);

        return to_route('categories.show', $category)->with('status', 'Categoría creada correctamente.');
    }

    public function show(Request $request, Category $category): View
    {
        $this->authorize('view', $category);
        $products = $category->products()->ownedBy($request->user())->with('catalog')->orderBy('name')->get();

        return view('categories.show', compact('category', 'products'));
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $category->update($request->validated());

        return to_route('categories.show', $category)->with('status', 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        // products.category_id tiene nullOnDelete: los productos quedan "sin categoría".
        $category->delete();

        return to_route('categories.index')->with('status', 'Categoría eliminada.');
    }
}
