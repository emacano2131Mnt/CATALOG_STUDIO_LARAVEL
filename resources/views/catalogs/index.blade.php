@extends('layouts.app')
@section('title', 'Catálogos · Catalog Studio')
@section('content')
<div class="page-head">
    <div><h1>Catálogos</h1><p class="muted">Tus colecciones de productos.</p></div>
    <a class="btn" href="{{ route('catalogs.create') }}"><i class="bi bi-plus-lg"></i> Nuevo catálogo</a>
</div>

<form method="GET" action="{{ route('catalogs.index') }}" class="filters">
    <div><label for="search">Buscar</label><input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Nombre o descripción"></div>
    <div style="flex:0"><button class="btn" type="submit"><i class="bi bi-search"></i> Buscar</button></div>
    @if (request('search'))<div style="flex:0"><a class="btn sec" href="{{ route('catalogs.index') }}">Limpiar</a></div>@endif
</form>

<div class="card">
    <table class="tbl">
        <thead><tr><th>Nombre</th><th>Categoría</th><th>Productos</th><th>Creado</th><th>Acciones</th></tr></thead>
        <tbody>
        @forelse ($catalogs as $catalog)
            <tr>
                <td><a href="{{ route('catalogs.show', $catalog) }}"><strong>{{ $catalog->name }}</strong></a></td>
                <td>{{ $catalog->category ?: '—' }}</td>
                <td><span class="badge ok">{{ $catalog->products_count }}</span></td>
                <td class="muted">{{ $catalog->created_at->format('d/m/Y') }}</td>
                <td>
                    <div class="actions">
                        <a class="btn sec sm" href="{{ route('catalogs.show', $catalog) }}">Ver</a>
                        <a class="btn sec sm" href="{{ route('catalogs.edit', $catalog) }}">Editar</a>
                        <form method="POST" action="{{ route('catalogs.duplicate', $catalog) }}">@csrf<button class="btn sec sm" type="submit">Duplicar</button></form>
                        <form method="POST" action="{{ route('catalogs.destroy', $catalog) }}" onsubmit="return confirm('¿Eliminar este catálogo y todos sus productos?')">@csrf @method('DELETE')<button class="btn danger sm" type="submit">Eliminar</button></form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty">No se encontraron catálogos. <a href="{{ route('catalogs.create') }}">Crea el primero</a>.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $catalogs->links('pagination::simple-default') }}
</div>
@endsection
