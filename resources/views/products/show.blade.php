@extends('layouts.app')
@section('title', $product->name.' · Catalog Studio')
@section('content')
<div class="page-head">
    <div><h1>{{ $product->name }}</h1><p class="muted">Catálogo: <a href="{{ route('catalogs.show', $product->catalog) }}">{{ $product->catalog->name }}</a></p></div>
    <div class="actions">
        <a class="btn sec" href="{{ route('products.edit', $product) }}"><i class="bi bi-pencil"></i> Editar</a>
        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('¿Eliminar este producto?')">@csrf @method('DELETE')<button class="btn danger" type="submit"><i class="bi bi-trash"></i> Eliminar</button></form>
    </div>
</div>
<div class="card grid g2">
    <div>@if ($product->image_src)<img class="thumb-lg" src="{{ $product->image_src }}" alt="{{ $product->name }}">@else<p class="empty">Sin fotografía</p>@endif</div>
    <div>
        <p><span class="badge ok" style="font-size:1.1rem">${{ number_format($product->price, 2) }}</span></p>
        <p><strong>Categoría:</strong> {{ $product->category?->name ?? 'Sin categoría' }}</p>
        <p><strong>Posición:</strong> {{ $product->position }}</p>
        <p>{{ $product->description ?: 'Sin descripción.' }}</p>
    </div>
</div>
@endsection
