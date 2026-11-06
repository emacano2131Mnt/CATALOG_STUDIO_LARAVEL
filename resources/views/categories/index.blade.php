@extends('layouts.app')
@section('title', 'Categorías · Catalog Studio')
@section('content')
<div class="page-head">
    <div><h1>Categorías</h1><p class="muted">Clasifica tus productos. Las categorías marcadas como "Global" son compartidas y no se pueden editar.</p></div>
    <a class="btn" href="{{ route('categories.create') }}"><i class="bi bi-plus-lg"></i> Nueva categoría</a>
</div>
<div class="card">
    <table id="categories-table" class="tbl display" style="width:100%">
        <thead><tr><th>Nombre</th><th>Tipo</th><th>Mis productos</th><th>Acciones</th></tr></thead>
        <tbody>
        @foreach ($categories as $category)
            <tr>
                <td><a href="{{ route('categories.show', $category) }}"><strong>{{ $category->name }}</strong></a></td>
                <td>{!! $category->isGlobal() ? '<span class="badge">Global</span>' : '<span class="badge ok">Propia</span>' !!}</td>
                <td data-order="{{ $category->products_count }}">{{ $category->products_count }}</td>
                <td>
                    <div class="actions">
                        <a class="btn sec sm" href="{{ route('categories.show', $category) }}">Ver</a>
                        @can('update', $category)
                            <a class="btn sec sm" href="{{ route('categories.edit', $category) }}">Editar</a>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('¿Eliminar esta categoría? Sus productos quedarán sin categoría.')">@csrf @method('DELETE')<button class="btn danger sm" type="submit">Eliminar</button></form>
                        @endcan
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@include('partials.datatables', ['table' => 'categories-table', 'orderColumn' => 0, 'noOrder' => '3'])
@endsection
