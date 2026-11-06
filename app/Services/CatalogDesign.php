<?php

namespace App\Services;

use App\Models\Catalog;

/**
 * Valores por defecto, plantillas y resolución de la configuración de diseño de un catálogo.
 * La configuración se guarda en catalogs.design (JSON). Esta clase garantiza que SIEMPRE existan
 * todas las claves, aunque el catálogo no tenga diseño guardado todavía.
 */
class CatalogDesign
{
    public const FONTS = [
        'sans' => ['label' => 'Sans (DejaVu Sans)', 'css' => '"DejaVu Sans", sans-serif'],
        'serif' => ['label' => 'Serif (DejaVu Serif)', 'css' => '"DejaVu Serif", serif'],
        'helvetica' => ['label' => 'Helvetica', 'css' => 'Helvetica, Arial, sans-serif'],
        'times' => ['label' => 'Times', 'css' => '"Times New Roman", Times, serif'],
        'mono' => ['label' => 'Monoespaciada', 'css' => '"DejaVu Sans Mono", monospace'],
    ];

    public const PHOTO_SIZES = ['small' => 70, 'medium' => 110, 'large' => 160];

    /** @return array<string, array<string, mixed>> */
    public static function templates(): array
    {
        $base = [
            'cover' => true, 'columns' => 2, 'photo_size' => 'medium', 'margin' => 15, 'spacing' => 12,
            'show_prices' => true, 'show_descriptions' => true, 'show_categories' => true,
            'subtitle' => '', 'cover_text' => '', 'header_text' => '', 'footer_text' => '', 'currency' => '$',
        ];

        return [
            'minimalista' => $base + [
                'label' => 'Minimalista', 'font' => 'helvetica',
                'primary_color' => '#111827', 'secondary_color' => '#6b7280', 'text_color' => '#1f2937', 'background_color' => '#ffffff',
            ],
            'premium' => array_merge($base, [
                'label' => 'Premium', 'font' => 'serif', 'columns' => 2, 'photo_size' => 'large', 'margin' => 20, 'spacing' => 16,
                'primary_color' => '#b8860b', 'secondary_color' => '#1f1a0e', 'text_color' => '#2b2416', 'background_color' => '#fbf7ee',
            ]),
            'comercial' => array_merge($base, [
                'label' => 'Comercial', 'font' => 'sans', 'columns' => 3, 'photo_size' => 'medium', 'margin' => 10, 'spacing' => 8,
                'primary_color' => '#dc2626', 'secondary_color' => '#f59e0b', 'text_color' => '#1f2937', 'background_color' => '#ffffff',
            ]),
            'corporativa' => array_merge($base, [
                'label' => 'Corporativa', 'font' => 'times', 'columns' => 2, 'photo_size' => 'medium', 'margin' => 18, 'spacing' => 12,
                'primary_color' => '#1e3a8a', 'secondary_color' => '#475569', 'text_color' => '#0f172a', 'background_color' => '#ffffff',
            ]),
        ];
    }

    /** Configuración efectiva: plantilla base + lo guardado + (opcional) valores de vista previa. */
    public static function resolve(Catalog $catalog, ?array $override = null): array
    {
        $templates = self::templates();
        $saved = $catalog->design ?? [];
        $input = array_merge($saved, $override ?? []);
        $key = $input['template'] ?? 'minimalista';
        $key = isset($templates[$key]) ? $key : 'minimalista';

        $design = array_merge($templates[$key], array_intersect_key($input, $templates[$key]));
        $design['template'] = $key;
        $design['title'] = ($input['title'] ?? null) ?: $catalog->name;
        $design['subtitle'] = $input['subtitle'] ?? $design['subtitle'];
        foreach (['cover', 'show_prices', 'show_descriptions', 'show_categories'] as $flag) {
            $design[$flag] = filter_var($design[$flag], FILTER_VALIDATE_BOOLEAN);
        }
        $design['font_css'] = self::FONTS[$design['font']]['css'] ?? self::FONTS['sans']['css'];
        $design['photo_px'] = self::PHOTO_SIZES[$design['photo_size']] ?? 110;

        return $design;
    }
}
