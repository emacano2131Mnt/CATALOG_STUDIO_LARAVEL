@extends('layouts.app')
@section('title', 'Importar productos · Catalog Studio')
@section('content')
<div class="page-head"><div><h1>Importar productos</h1><p class="muted">Productos de ejemplo desde la API externa <a href="https://dummyjson.com/products" target="_blank" rel="noopener">DummyJSON</a>.</p></div></div>

@if ($error)
    <div class="alert err" role="alert"><i class="bi bi-wifi-off"></i> {{ $error }} <a href="{{ request()->fullUrl() }}">Reintentar</a></div>
@endif

<form method="GET" action="{{ route('import.index') }}" class="filters">
    <div><label for="q">Buscar en DummyJSON</label><input id="q" type="search" name="q" value="{{ $q }}" placeholder="phone, laptop, perfume…"></div>
    <div style="flex:0"><button class="btn" type="submit"><i class="bi bi-search"></i> Buscar</button></div>
</form>

@if ($result)
    @if ($catalogs->isEmpty())
        <div class="alert err">Necesitas un catálogo para importar. <a href="{{ route('catalogs.create') }}">Crear catálogo</a></div>
    @endif
    <form method="POST" action="{{ route('import.store') }}">
        @csrf
        <div class="card filters" style="align-items:end">
            <div><label for="catalog_id">Importar al catálogo *</label><select id="catalog_id" name="catalog_id" required><option value="">Seleccione…</option>@foreach ($catalogs as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
            <div style="flex:0"><button class="btn ok" type="submit" @disabled($catalogs->isEmpty())><i class="bi bi-cloud-download"></i> Importar seleccionados</button></div>
            <div class="muted" style="flex:0;white-space:nowrap">{{ $result['total'] }} resultado(s)</div>
        </div>

        <div class="grid g4">
            @forelse ($result['products'] as $item)
                <label class="product-card" style="cursor:pointer">
                    @if ($item['thumbnail'])<img src="{{ $item['thumbnail'] }}" alt="{{ $item['title'] }}" loading="lazy">@endif
                    <div class="body">
                        <input type="checkbox" name="ids[]" value="{{ $item['id'] }}"> <strong>{{ $item['title'] }}</strong>
                        <div class="muted">{{ \Illuminate\Support\Str::limit($item['description'], 80) }}</div>
                        <div><span class="badge">{{ \Illuminate\Support\Str::of($item['category'])->replace('-', ' ')->title() }}</span> <span class="badge ok">${{ number_format($item['price'], 2) }}</span></div>
                        @if ($imported->has($item['id']))<div class="muted">Ya importado en {{ count($imported[$item['id']]) }} catálogo(s)</div>@endif
                    </div>
                </label>
            @empty
                <p class="empty" style="grid-column:1/-1">Sin resultados.</p>
            @endforelse
        </div>
    </form>

    <div class="pager">
        @if ($page > 1)<a class="btn sec sm" href="{{ route('import.index', ['q' => $q, 'page' => $page - 1]) }}">« Anterior</a>@endif
        <span class="muted">Página {{ $page }} de {{ max($pages, 1) }}</span>
        @if ($page < $pages)<a class="btn sec sm" href="{{ route('import.index', ['q' => $q, 'page' => $page + 1]) }}">Siguiente »</a>@endif
    </div>
@endif
@endsection
