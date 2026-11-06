<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    public function rules(): array { return ['name' => ['required', 'string', 'max:80', Rule::unique('categories', 'name')->ignore($this->route('category'))]]; }
}
