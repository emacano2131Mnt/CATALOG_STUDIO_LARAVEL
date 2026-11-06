@extends('layouts.app')
@section('title', 'Nuevo producto · Catalog Studio')
@section('content')
<div class="page-head"><h1>Nuevo producto</h1></div>
<div class="card" style="max-width:800px">
    @if ($catalogs->isEmpty())
        <p class="empty">Primero debes <a href="{{ route('catalogs.create') }}">crear un catálogo</a>.</p>
    @else
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">@include('products._form')</form>
    @endif
</div>
@endsection
