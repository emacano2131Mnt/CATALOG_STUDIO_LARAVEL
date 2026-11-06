<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        return view('categories.index', ['categories' => Category::withCount('products')->search($request->string('search')->toString())->orderBy('name')->paginate(20)->withQueryString()]);
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());
        return to_route('categories.show', $category)->with('status', 'Categoría creada.');
    }

    public function show(Category $category): View
    {
        return view('categories.show', ['category' => $category->load('products')]);
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());
        return to_route('categories.show', $category)->with('status', 'Categoría actualizada.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();
        return to_route('categories.index')->with('status', 'Categoría eliminada.');
    }
}
