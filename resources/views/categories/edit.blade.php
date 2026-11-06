@extends('layouts.app')
@section('title', 'Editar categoría · Catalog Studio')
@section('content')
<div class="page-head"><h1>Editar categoría</h1></div>
<div class="card" style="max-width:500px">
    <form method="POST" action="{{ route('categories.update', $category) }}">
        @csrf @method('PUT')
        <div class="field"><label for="name">Nombre *</label><input id="name" type="text" name="name" value="{{ old('name', $category->name) }}" required maxlength="80"></div>
        <div class="actions"><button class="btn" type="submit">Guardar</button><a class="btn sec" href="{{ route('categories.index') }}">Cancelar</a></div>
    </form>
</div>
@endsection
