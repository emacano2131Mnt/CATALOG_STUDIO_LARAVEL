<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('category')) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:80', Rule::unique('categories', 'name')->where('user_id', $this->user()->id)->ignore($this->route('category'))]];
    }
}
