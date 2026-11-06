@extends('layouts.guest')
@section('title', 'Recuperar contraseña · Catalog Studio')
@section('content')
<h1>Recuperar contraseña</h1>
<p class="muted">Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>
<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="field">
        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        @error('email')<div class="error-text">{{ $message }}</div>@enderror
    </div>
    <button class="btn" type="submit" style="width:100%;justify-content:center">Enviar enlace</button>
</form>
<p class="muted" style="margin-top:1rem"><a href="{{ route('login') }}">Volver a iniciar sesión</a></p>
@endsection
