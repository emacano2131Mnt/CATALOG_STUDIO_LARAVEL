@extends('layouts.app')
@section('title', 'Diseñador · Catalog Studio')
@section('content')
<div class="page-head"><div><h1>Diseñador</h1><p class="muted">Elige un catálogo para personalizar su diseño y exportarlo a PDF.</p></div></div>
<div class="grid g3">
    @forelse ($catalogs as $catalog)
        <div class="card">
            <h3>{{ $catalog->name }}</h3>
            <p class="muted">{{ $catalog->products_count }} producto(s) · Plantilla: {{ ucfirst($catalog->design['template'] ?? 'minimalista') }}</p>
            <a class="btn" href="{{ route('designer.edit', $catalog) }}"><i class="bi bi-palette"></i> Diseñar</a>
        </div>
    @empty
        <div class="card empty" style="grid-column:1/-1">Primero <a href="{{ route('catalogs.create') }}">crea un catálogo</a>.</div>
    @endforelse
</div>
@endsection
