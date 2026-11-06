<?php

namespace App\Http\Requests;

/** Misma validación que al crear; la autorización (CatalogPolicy) se ejecuta ANTES de validar. */
class UpdateCatalogRequest extends StoreCatalogRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('catalog')) ?? false;
    }
}
