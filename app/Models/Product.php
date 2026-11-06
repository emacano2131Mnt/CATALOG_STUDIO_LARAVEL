<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['catalog_id', 'category_id', 'name', 'description', 'price', 'image', 'image_url', 'position', 'external_id'];

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

    /** URL pública de la imagen (archivo subido o URL remota importada), o null. */
    public function getImageSrcAttribute(): ?string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return Storage::disk('public')->url($this->image);
        }

        return $this->image_url;
    }

    /** Ruta/URL que Dompdf puede leer: archivo local si existe, si no la URL remota. */
    public function getImagePdfSourceAttribute(): ?string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return Storage::disk('public')->path($this->image);
        }

        return $this->image_url;
    }

    /** Productos cuyo catálogo pertenece al usuario. */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->whereHas('catalog', fn (Builder $q) => $q->where('user_id', $user->id));
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
