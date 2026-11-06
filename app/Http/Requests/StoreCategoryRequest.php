<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }

    public function rules(): array
    {
        // El nombre es único solo dentro de las categorías del propio usuario.
        return ['name' => ['required', 'string', 'max:80', Rule::unique('categories', 'name')->where('user_id', $this->user()->id)]];
    }

    public function attributes(): array
    {
        return ['name' => 'nombre'];
    }
}
