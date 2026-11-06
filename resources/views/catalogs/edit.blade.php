@extends('layouts.app')
@section('title', 'Editar catálogo · Catalog Studio')
@section('content')
<div class="page-head"><h1>Editar catálogo</h1></div>
<div class="card" style="max-width:700px">
    <form method="POST" action="{{ route('catalogs.update', $catalog) }}">@method('PUT') @include('catalogs._form')</form>
</div>
@endsection
