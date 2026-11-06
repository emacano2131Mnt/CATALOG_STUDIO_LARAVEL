@extends('layouts.app')
@section('title', 'Productos · Catalog Studio')
@section('content')
<div class="page-head">
    <div><h1>Productos</h1><p class="muted">Todos los productos de tus catálogos.</p></div>
    <a class="btn" href="{{ route('products.create') }}"><i class="bi bi-plus-lg"></i> Nuevo producto</a>
</div>

<form method="GET" action="{{ route('products.index') }}" class="filters">
    <div>
        <label for="catalog_id">Catálogo</label>
        <select id="catalog_id" name="catalog_id"><option value="">Todos</option>@foreach ($catalogs as $c)<option value="{{ $c->id }}" @selected(request('catalog_id') == $c->id)>{{ $c->name }}</option>@endforeach</select>
    </div>
    <div>
        <label for="category_id">Categoría</label>
        <select id="category_id" name="category_id"><option value="">Todas</option>@foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>@endforeach</select>
    </div>
    <div style="flex:0"><button class="btn" type="submit"><i class="bi bi-funnel"></i> Filtrar</button></div>
    @if (request()->hasAny(['catalog_id', 'category_id']))<div style="flex:0"><a class="btn sec" href="{{ route('products.index') }}">Limpiar</a></div>@endif
</form>

<div class="card">
    <table id="products-table" class="tbl display" style="width:100%">
        <thead><tr><th>Foto</th><th>Nombre</th><th>Catálogo</th><th>Categoría</th><th>Precio</th><th>Acciones</th></tr></thead>
        <tbody>
        @foreach ($products as $product)
            <tr>
                <td>@if ($product->image_src)<img class="thumb" src="{{ $product->image_src }}" alt="{{ $product->name }}" loading="lazy">@endif</td>
                <td><a href="{{ route('products.show', $product) }}"><strong>{{ $product->name }}</strong></a></td>
                <td>{{ $product->catalog->name }}</td>
                <td>{{ $product->category?->name ?? '—' }}</td>
                <td data-order="{{ $product->price }}">${{ number_format($product->price, 2) }}</td>
                <td>
                    <div class="actions">
                        <a class="btn sec sm" href="{{ route('products.edit', $product) }}">Editar</a>
                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('¿Eliminar este producto?')">@csrf @method('DELETE')<button class="btn danger sm" type="submit">Eliminar</button></form>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@include('partials.datatables', ['table' => 'products-table', 'orderColumn' => 1, 'noOrder' => '[0, 5]'])
@endsection
