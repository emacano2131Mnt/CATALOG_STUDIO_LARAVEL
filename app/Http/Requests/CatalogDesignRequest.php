<?php

namespace App\Http\Requests;

use App\Services\CatalogDesign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Valida la configuración del diseñador (se usa al guardar y al generar la vista previa). */
class CatalogDesignRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }

    public function rules(): array
    {
        $hex = ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'];

        return [
            'design' => ['sometimes', 'array'],
            'design.template' => ['nullable', Rule::in(array_keys(CatalogDesign::templates()))],
            'design.primary_color' => $hex,
            'design.secondary_color' => $hex,
            'design.text_color' => $hex,
            'design.background_color' => $hex,
            'design.font' => ['nullable', Rule::in(array_keys(CatalogDesign::FONTS))],
            'design.title' => ['nullable', 'string', 'max:120'],
            'design.subtitle' => ['nullable', 'string', 'max:200'],
            'design.cover_text' => ['nullable', 'string', 'max:500'],
            'design.header_text' => ['nullable', 'string', 'max:150'],
            'design.footer_text' => ['nullable', 'string', 'max:150'],
            'design.currency' => ['nullable', 'string', 'max:5'],
            'design.columns' => ['nullable', 'integer', 'between:1,4'],
            'design.photo_size' => ['nullable', Rule::in(array_keys(CatalogDesign::PHOTO_SIZES))],
            'design.margin' => ['nullable', 'integer', 'between:5,40'],
            'design.spacing' => ['nullable', 'integer', 'between:0,40'],
            'design.cover' => ['nullable', 'boolean'],
            'design.show_prices' => ['nullable', 'boolean'],
            'design.show_descriptions' => ['nullable', 'boolean'],
            'design.show_categories' => ['nullable', 'boolean'],
        ];
    }

    /** Diseño ya saneado. Los checkboxes ausentes se interpretan como "false". */
    public function design(): array
    {
        $input = $this->validated()['design'] ?? [];
        foreach (['cover', 'show_prices', 'show_descriptions', 'show_categories'] as $flag) {
            $input[$flag] = (bool) ($input[$flag] ?? false);
        }

        return array_filter($input, fn ($v) => $v !== null);
    }
}
