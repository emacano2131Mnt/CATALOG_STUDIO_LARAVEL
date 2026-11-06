<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Informe de productos</title>
<style>
    @page { margin: 15mm; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #1f2937 }
    h1 { color: #6d28d9; font-size: 20px; margin: 0 }
    .meta { color: #6b7280; margin-bottom: 12px }
    table { width: 100%; border-collapse: collapse }
    th { background: #6d28d9; color: #fff; text-align: left; padding: 5px }
    td { padding: 5px; border-bottom: 1px solid #e5e7eb; vertical-align: middle }
    tr:nth-child(even) td { background: #f5f3ff }
    .r { text-align: right }
    img { width: 32px; height: 32px }
    .tot { margin-top: 10px; text-align: right; font-weight: bold; color: #16a34a; font-size: 12px }
</style>
</head>
<body>
<h1>Informe de productos</h1>
<div class="meta">Generado para {{ $user->name }} · {{ $generatedAt->format('d/m/Y H:i') }} · {{ $products->count() }} producto(s)</div>
<table>
    <thead><tr><th>Foto</th><th>Producto</th><th>Catálogo</th><th>Categoría</th><th class="r">Precio</th></tr></thead>
    <tbody>
    @forelse ($products as $product)
        <tr>
            <td>@if ($product->image_pdf_source)<img src="{{ $product->image_pdf_source }}" alt="">@endif</td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->catalog->name }}</td>
            <td>{{ $product->category?->name ?? '—' }}</td>
            <td class="r">${{ number_format($product->price, 2) }}</td>
        </tr>
    @empty
        <tr><td colspan="5">No hay productos para los filtros seleccionados.</td></tr>
    @endforelse
    </tbody>
</table>
<div class="tot">Valor total listado: ${{ number_format($total, 2) }}</div>
</body>
</html>
