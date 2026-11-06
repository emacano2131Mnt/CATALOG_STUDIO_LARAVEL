@extends('layouts.app')
@section('title', $product->name)
@section('content')<h1>{{ $product->name }}</h1><p>{{ $product->description }}</p>@endsection
