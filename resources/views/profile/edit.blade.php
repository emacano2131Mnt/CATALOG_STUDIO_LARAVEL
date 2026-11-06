@extends('layouts.app')
@section('title', 'Mi perfil · Catalog Studio')
@section('content')
<div class="page-head"><div><h1>Mi perfil</h1><p class="muted">Administra tu información de cuenta.</p></div></div>

<div class="grid g2">
    <div class="card">
        <h2>Información de la cuenta</h2>
        <p><span class="avatar">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span></p>
        <p class="muted">Miembro desde {{ $user->created_at?->translatedFormat('d \d\e F \d\e Y') }} · {{ $catalogsCount }} catálogo(s)</p>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PUT')
            <div class="field"><label for="name">Nombre</label><input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required></div>
            <div class="field"><label for="email">Correo electrónico</label><input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required></div>
            <button class="btn" type="submit">Guardar cambios</button>
        </form>
    </div>

    <div class="card">
        <h2>Cambiar contraseña</h2>
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf @method('PUT')
            <div class="field"><label for="current_password">Contraseña actual</label><input id="current_password" type="password" name="current_password" required autocomplete="current-password"></div>
            <div class="field"><label for="password">Nueva contraseña</label><input id="password" type="password" name="password" required autocomplete="new-password"><div class="hint">Mínimo 8 caracteres, con letras y números.</div></div>
            <div class="field"><label for="password_confirmation">Confirmar nueva contraseña</label><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"></div>
            <button class="btn" type="submit">Actualizar contraseña</button>
        </form>
    </div>
</div>

<div class="card">
    <h2>Tokens de la API</h2>
    <p class="muted">Usa un token como <code>Authorization: Bearer &lt;token&gt;</code> para probar la API en Postman o Insomnia.</p>
    @if (session('new_token'))
        <div class="alert ok"><strong>Su nuevo token (cópielo ahora):</strong><br><code style="word-break:break-all" id="new-token">{{ session('new_token') }}</code></div>
    @endif
    <form method="POST" action="{{ route('profile.tokens.store') }}" class="filters">
        @csrf
        <div><label for="token_name">Nombre del token</label><input id="token_name" type="text" name="token_name" placeholder="Ej: Postman" required maxlength="60"></div>
        <div style="flex:0"><button class="btn" type="submit"><i class="bi bi-key"></i> Crear token</button></div>
    </form>
    <table class="tbl">
        <tbody>
        @forelse ($tokens as $token)
            <tr>
                <td>{{ $token->name }}</td>
                <td class="muted">Creado {{ $token->created_at->diffForHumans() }} · Último uso: {{ $token->last_used_at?->diffForHumans() ?? 'nunca' }}</td>
                <td><form method="POST" action="{{ route('profile.tokens.destroy', $token->id) }}" onsubmit="return confirm('¿Revocar este token?')">@csrf @method('DELETE')<button class="btn danger sm" type="submit">Revocar</button></form></td>
            </tr>
        @empty
            <tr><td class="empty">No tienes tokens creados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
