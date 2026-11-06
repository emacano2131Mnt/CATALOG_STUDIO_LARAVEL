@extends('layouts.app')
@section('title', 'Editar producto · Catalog Studio')
@section('content')
<div class="page-head"><h1>Editar producto</h1></div>
<div class="card" style="max-width:800px">
    <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">@method('PUT') @include('products._form')</form>
</div>
@endsection
