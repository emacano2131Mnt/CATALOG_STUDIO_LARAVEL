<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCatalogRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['name' => ['required', 'string', 'max:120'], 'category' => ['nullable', 'string', 'max:80'], 'description' => ['nullable', 'string', 'max:10000']]; }
}
