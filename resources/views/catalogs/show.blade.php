@extends('layouts.app')
@section('title', $catalog->name.' · Catalog Studio')
@section('content')
<div class="page-head">
    <div><h1>{{ $catalog->name }}</h1><p class="muted">{{ $catalog->category ?: 'Sin categoría de referencia' }} · {{ $catalog->products->count() }} producto(s)</p></div>
    <div class="actions">
        <a class="btn sec" href="{{ route('designer.edit', $catalog) }}"><i class="bi bi-palette"></i> Diseñador</a>
        <a class="btn sec" href="{{ route('catalogs.edit', $catalog) }}"><i class="bi bi-pencil"></i> Editar</a>
        <form method="POST" action="{{ route('catalogs.duplicate', $catalog) }}">@csrf<button class="btn sec" type="submit"><i class="bi bi-files"></i> Duplicar</button></form>
        <form method="POST" action="{{ route('catalogs.destroy', $catalog) }}" onsubmit="return confirm('¿Eliminar este catálogo y todos sus productos?')">@csrf @method('DELETE')<button class="btn danger" type="submit"><i class="bi bi-trash"></i> Eliminar</button></form>
    </div>
</div>

@if ($catalog->description)<div class="card"><p>{{ $catalog->description }}</p></div>@endif

<div class="card">
    <div class="page-head" style="margin-bottom:.5rem">
        <h2 style="margin:0">Productos del catálogo</h2>
        <a class="btn sm" href="{{ route('products.create', ['catalog_id' => $catalog->id]) }}"><i class="bi bi-plus-lg"></i> Agregar producto</a>
    </div>

    @if ($catalog->products->isEmpty())
        <p class="empty">Este catálogo aún no tiene productos.</p>
    @else
        {{-- Formulario GET: exportar PDF completo o solo los productos marcados --}}
        <form method="GET" action="{{ route('catalogs.pdf', $catalog) }}" target="_blank" id="export-form">
            <table class="tbl">
                <thead><tr><th style="width:30px"><input type="checkbox" id="check-all" aria-label="Seleccionar todos"></th><th>Foto</th><th>Producto</th><th>Categoría</th><th>Precio</th><th>Orden</th></tr></thead>
                <tbody>
                @foreach ($catalog->products as $product)
                    <tr>
                        <td><input type="checkbox" name="products[]" value="{{ $product->id }}" class="check-item" aria-label="Seleccionar {{ $product->name }}"></td>
                        <td>@if ($product->image_src)<img class="thumb" src="{{ $product->image_src }}" alt="{{ $product->name }}">@else<span class="thumb" style="display:inline-block"></span>@endif</td>
                        <td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td>
                        <td>{{ $product->category?->name ?? '—' }}</td>
                        <td>${{ number_format($product->price, 2) }}</td>
                        <td>
                            <div class="actions">
                                <button class="btn sec sm" type="submit" form="move-{{ $product->id }}-up" title="Subir" @disabled($loop->first)><i class="bi bi-arrow-up"></i></button>
                                <button class="btn sec sm" type="submit" form="move-{{ $product->id }}-down" title="Bajar" @disabled($loop->last)><i class="bi bi-arrow-down"></i></button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="actions" style="margin-top:1rem">
                <button class="btn" type="submit" name="download" value="0"><i class="bi bi-eye"></i> Ver PDF completo / seleccionados</button>
                <button class="btn ok" type="submit" name="download" value="1"><i class="bi bi-download"></i> Descargar PDF</button>
                <span class="muted">Sin marcar productos se exporta todo el catálogo.</span>
            </div>
        </form>
        @foreach ($catalog->products as $product)
            <form id="move-{{ $product->id }}-up" method="POST" action="{{ route('products.move', $product) }}">@csrf @method('PATCH')<input type="hidden" name="direction" value="up"></form>
            <form id="move-{{ $product->id }}-down" method="POST" action="{{ route('products.move', $product) }}">@csrf @method('PATCH')<input type="hidden" name="direction" value="down"></form>
        @endforeach
    @endif
</div>
@endsection

@push('scripts')
<script>
    const all = document.getElementById('check-all');
    if (all) all.addEventListener('change', () => document.querySelectorAll('.check-item').forEach(c => c.checked = all.checked));
</script>
@endpush
