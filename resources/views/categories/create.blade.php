@extends('layouts.app')
@section('title', 'Nueva categoría · Catalog Studio')
@section('content')
<div class="page-head"><h1>Nueva categoría</h1></div>
<div class="card" style="max-width:500px">
    <form method="POST" action="{{ route('categories.store') }}">
        @csrf
        <div class="field"><label for="name">Nombre *</label><input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="80" autofocus></div>
        <div class="actions"><button class="btn" type="submit">Guardar</button><a class="btn sec" href="{{ route('categories.index') }}">Cancelar</a></div>
    </form>
</div>
@endsection
