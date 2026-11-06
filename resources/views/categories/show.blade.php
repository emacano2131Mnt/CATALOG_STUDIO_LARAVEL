@extends('layouts.app')
@section('title', $category->name.' · Catalog Studio')
@section('content')
<div class="page-head">
    <div><h1>{{ $category->name }}</h1><p class="muted">{{ $category->isGlobal() ? 'Categoría global (solo lectura)' : 'Categoría propia' }} · {{ $products->count() }} de tus productos</p></div>
    @can('update', $category)<a class="btn sec" href="{{ route('categories.edit', $category) }}"><i class="bi bi-pencil"></i> Editar</a>@endcan
</div>
<div class="card">
    <table class="tbl">
        <thead><tr><th>Producto</th><th>Catálogo</th><th>Precio</th></tr></thead>
        <tbody>
        @forelse ($products as $product)
            <tr><td><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></td><td>{{ $product->catalog->name }}</td><td>${{ number_format($product->price, 2) }}</td></tr>
        @empty
            <tr><td colspan="3" class="empty">No tienes productos en esta categoría.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
