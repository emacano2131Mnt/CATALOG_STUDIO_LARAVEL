@extends('layouts.guest')
@section('title', 'Restablecer contraseña · Catalog Studio')
@section('content')
<h1>Nueva contraseña</h1>
<form method="POST" action="{{ route('password.store') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="field">
        <label for="email">Correo electrónico</label>
        <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required>
        @error('email')<div class="error-text">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="password">Nueva contraseña</label>
        <input id="password" type="password" name="password" required autocomplete="new-password">
        <div class="hint">Mínimo 8 caracteres, con letras y números.</div>
        @error('password')<div class="error-text">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="password_confirmation">Confirmar contraseña</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
    </div>
    <button class="btn" type="submit" style="width:100%;justify-content:center">Restablecer contraseña</button>
</form>
@endsection
