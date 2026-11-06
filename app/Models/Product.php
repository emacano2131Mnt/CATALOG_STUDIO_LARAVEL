<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = ['catalog_id', 'category_id', 'name', 'description', 'price', 'image', 'position'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'position' => 'integer'];
    }

    /** @return BelongsTo<Catalog, $this> */
    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $query) => $query->where(function (Builder $query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        }));
    }

    public function scopeInCatalog(Builder $query, ?int $catalogId): Builder
    {
        return $query->when($catalogId, fn (Builder $query) => $query->where('catalog_id', $catalogId));
    }

    public function scopeInCategory(Builder $query, ?int $categoryId): Builder
    {
        return $query->when($categoryId, fn (Builder $query) => $query->where('category_id', $categoryId));
    }
}
