<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate(['per_page' => ['nullable', 'integer', 'between:1,100'], 'search' => ['nullable', 'string', 'max:100']]);
        $user = $request->user();

        $categories = Category::visibleTo($user)
            ->withCount(['products' => fn (Builder $q) => $q->ownedBy($user)])
            ->search($data['search'] ?? null)->orderBy('name')
            ->paginate($data['per_page'] ?? 15)->withQueryString();

        return CategoryResource::collection($categories);
    }
}
