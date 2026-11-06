{{-- Plantilla única: se usa para la vista previa HTML ($pdf=false) y para el PDF de Dompdf ($pdf=true). --}}
@php
    $cols = max(1, (int) $design['columns']);
    $chunks = $products->chunk($cols);
    $cellWidth = floor(100 / $cols);
    $photo = $design['photo_px'];
    $money = fn ($v) => $design['currency'].number_format($v, 2);
@endphp
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>{{ $design['title'] }}</title>
<style>
    @if ($pdf) @page { margin: {{ $design['margin'] }}mm; } @endif
    body { margin: 0; font-family: {!! $design['font_css'] !!}; color: {{ $design['text_color'] }}; background: {{ $design['background_color'] }}; font-size: 11px; @unless($pdf) padding: {{ $design['margin'] }}mm; @endunless }
    h1 { color: {{ $design['primary_color'] }}; margin: 0 0 4px; }
    .cover { text-align: center; padding: 120px 20px; border: 4px solid {{ $design['primary_color'] }}; @if($pdf) page-break-after: always; @endif margin-bottom: 20px }
    .cover h1 { font-size: 34px }
    .cover .sub { color: {{ $design['secondary_color'] }}; font-size: 16px; margin-top: 8px }
    .cover .txt { margin-top: 30px; color: {{ $design['text_color'] }} }
    .hdr { border-bottom: 2px solid {{ $design['primary_color'] }}; padding-bottom: 4px; margin-bottom: 12px; color: {{ $design['secondary_color'] }}; font-size: 10px }
    table.grid { width: 100%; border-collapse: separate; border-spacing: {{ $design['spacing'] }}px }
    td.cell { width: {{ $cellWidth }}%; vertical-align: top; border: 1px solid {{ $design['secondary_color'] }}33; padding: 8px; }
    .photo { width: {{ $photo }}px; height: {{ $photo }}px; object-fit: cover; display: block; margin: 0 auto 6px }
    .name { font-weight: bold; font-size: 12px; color: {{ $design['primary_color'] }} }
    .cat { color: {{ $design['secondary_color'] }}; font-size: 9px; text-transform: uppercase }
    .price { font-weight: bold; font-size: 13px; margin-top: 4px; color: {{ $design['secondary_color'] }} }
    .desc { margin-top: 4px; font-size: 10px }
    .ftr { margin-top: 14px; border-top: 1px solid {{ $design['secondary_color'] }}; padding-top: 4px; text-align: center; color: {{ $design['secondary_color'] }}; font-size: 9px }
    @if ($pdf) .ftr { position: fixed; bottom: -8mm; left: 0; right: 0; margin: 0 } @endif
    @unless ($pdf) body { max-width: 820px; margin: 0 auto; } @endunless
</style>
</head>
<body>
@if ($design['cover'])
    <div class="cover">
        <h1>{{ $design['title'] }}</h1>
        @if ($design['subtitle'])<div class="sub">{{ $design['subtitle'] }}</div>@endif
        @if ($design['cover_text'])<div class="txt">{{ $design['cover_text'] }}</div>@endif
    </div>
@endif

@if ($design['header_text'])<div class="hdr">{{ $design['header_text'] }}</div>@endif

@if ($products->isEmpty())
    <p>Este catálogo no tiene productos.</p>
@else
    <table class="grid">
        @foreach ($chunks as $row)
            <tr>
                @foreach ($row as $product)
                    <td class="cell">
                        @if ($design['photo_size'] && ($src = $pdf ? $product->image_pdf_source : $product->image_src))
                            <img class="photo" src="{{ $src }}" alt="">
                        @endif
                        @if ($design['show_categories'] && $product->category)<div class="cat">{{ $product->category->name }}</div>@endif
                        <div class="name">{{ $product->name }}</div>
                        @if ($design['show_descriptions'] && $product->description)<div class="desc">{{ \Illuminate\Support\Str::limit($product->description, 160) }}</div>@endif
                        @if ($design['show_prices'])<div class="price">{{ $money($product->price) }}</div>@endif
                    </td>
                @endforeach
                @for ($i = $row->count(); $i < $cols; $i++)<td class="cell" style="border:0"></td>@endfor
            </tr>
        @endforeach
    </table>
@endif

@if ($design['footer_text'])<div class="ftr">{{ $design['footer_text'] }}</div>@endif
</body>
</html>
