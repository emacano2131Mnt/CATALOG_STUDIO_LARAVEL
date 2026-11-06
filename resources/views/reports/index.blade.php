@extends('layouts.app')
@section('title', 'Informes · Catalog Studio')
@section('content')
<div class="page-head"><div><h1>Informes</h1><p class="muted">Genera y descarga informes y catálogos en PDF.</p></div></div>

<div class="card">
    <h2>Informe de productos</h2>
    <form method="GET" action="{{ route('reports.products') }}" target="_blank" class="filters">
        <div><label for="catalog_id">Catálogo</label><select id="catalog_id" name="catalog_id"><option value="">Todos</option>@foreach ($catalogs as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
        <div><label for="category_id">Categoría</label><select id="category_id" name="category_id"><option value="">Todas</option>@foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
        <div style="flex:0" class="actions">
            <button class="btn" type="submit" name="download" value="0"><i class="bi bi-eye"></i> Ver</button>
            <button class="btn ok" type="submit" name="download" value="1"><i class="bi bi-download"></i> Descargar</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Catálogos en PDF</h2>
    <table class="tbl">
        <thead><tr><th>Catálogo</th><th>Productos</th><th>Acciones</th></tr></thead>
        <tbody>
        @forelse ($catalogs as $catalog)
            <tr>
                <td><a href="{{ route('catalogs.show', $catalog) }}">{{ $catalog->name }}</a></td>
                <td>{{ $catalog->products_count }}</td>
                <td class="actions">
                    <a class="btn sec sm" target="_blank" href="{{ route('catalogs.pdf', $catalog) }}">Ver PDF</a>
                    <a class="btn sec sm" href="{{ route('catalogs.pdf', [$catalog, 'download' => 1]) }}">Descargar</a>
                    <a class="btn sec sm" href="{{ route('designer.edit', $catalog) }}">Diseño</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="empty">Aún no tienes catálogos.</td></tr>
        @endforelse
        </tbody>
    </table>
    <p class="muted">Para exportar solo algunos productos, márcalos en la página del catálogo.</p>
</div>
@endsection
