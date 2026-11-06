<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }

    public function rules(): array
    {
        $user = $this->user();

        return [
            // El catálogo debe existir Y pertenecer al usuario autenticado.
            'catalog_id' => ['required', 'integer', Rule::exists('catalogs', 'id')->where('user_id', $user->id)],
            // La categoría debe ser propia o global.
            'category_id' => ['nullable', 'integer', function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                if ($value !== null && ! Category::visibleTo($user)->whereKey($value)->exists()) {
                    $fail('La categoría seleccionada no es válida.');
                }
            }],
            'name' => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:10000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'position' => ['sometimes', 'nullable', 'integer', 'min:0'],
            // Fotografía: solo imágenes raster (se excluye SVG por poder contener scripts), máx. 2 MB.
            'photo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'mimetypes:image/jpeg,image/png,image/webp,image/gif', 'max:2048'],
            'remove_photo' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'catalog_id' => 'catálogo', 'category_id' => 'categoría', 'name' => 'nombre',
            'description' => 'descripción', 'price' => 'precio', 'position' => 'posición', 'photo' => 'fotografía',
        ];
    }
}
