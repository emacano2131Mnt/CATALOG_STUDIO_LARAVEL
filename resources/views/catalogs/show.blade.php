@extends('layouts.app')
@section('title', $catalog->name)
@section('content')<h1>{{ $catalog->name }}</h1><p>{{ $catalog->description }}</p>@endsection
