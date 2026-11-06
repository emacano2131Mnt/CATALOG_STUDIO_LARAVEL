@extends('layouts.guest')
@section('title', 'Iniciar sesión · Catalog Studio')
@section('content')
<h1>Iniciar sesión</h1>
<p class="muted">Ingresa para administrar tus catálogos.</p>
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="field">
        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email')<div class="error-text">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="password">Contraseña</label>
        <input id="password" type="password" name="password" required autocomplete="current-password">
        @error('password')<div class="error-text">{{ $message }}</div>@enderror
    </div>
    <div class="field"><label class="check"><input type="checkbox" name="remember" value="1"> Recordarme</label></div>
    <button class="btn" type="submit" style="width:100%;justify-content:center">Entrar</button>
</form>
<p class="muted" style="margin-top:1rem"><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a><br>¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
@endsection
