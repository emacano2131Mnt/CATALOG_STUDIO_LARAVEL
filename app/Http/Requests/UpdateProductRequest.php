<?php

namespace App\Http\Requests;

/** Misma validación que al crear; la autorización (ProductPolicy) se ejecuta ANTES de validar. */
class UpdateProductRequest extends StoreProductRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('product')) ?? false;
    }
}
