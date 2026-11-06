@extends('layouts.app')
@section('title', 'Nuevo catálogo · Catalog Studio')
@section('content')
<div class="page-head"><h1>Nuevo catálogo</h1></div>
<div class="card" style="max-width:700px">
    <form method="POST" action="{{ route('catalogs.store') }}">@include('catalogs._form')</form>
</div>
@endsection
