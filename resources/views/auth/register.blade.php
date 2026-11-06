@extends('layouts.guest')
@section('title', 'Crear cuenta · Catalog Studio')
@section('content')
<h1>Crear cuenta</h1>
<p class="muted">Empieza a crear tus catálogos hoy.</p>
<form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="field">
        <label for="name">Nombre</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        @error('name')<div class="error-text">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        @error('email')<div class="error-text">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" required autocomplete="new-password">
        <div class="hint">Mínimo 8 caracteres, con letras y números.</div>
        @error('password')<div class="error-text">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="password_confirmation">Confirmar contraseña</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
    </div>
    <button class="btn" type="submit" style="width:100%;justify-content:center">Registrarme</button>
</form>
<p class="muted" style="margin-top:1rem">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
@endsection
