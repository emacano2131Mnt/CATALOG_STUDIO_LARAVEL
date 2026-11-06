<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return ['catalog_id' => ['required', 'integer', Rule::exists('catalogs', 'id')], 'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')], 'name' => ['required', 'string', 'max:140'], 'description' => ['nullable', 'string', 'max:10000'], 'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'], 'image' => ['nullable', 'string', 'max:255'], 'position' => ['sometimes', 'integer', 'min:0']];
    }
}
