<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'catalogsCount' => $user->catalogs()->count(),
            'tokens' => $user->tokens()->latest()->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [], ['name' => 'nombre', 'email' => 'correo electrónico']);

        $user->update($data);

        return to_route('profile.edit')->with('status', 'Perfil actualizado correctamente.');
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers(), 'different:current_password'],
        ], [], ['current_password' => 'contraseña actual', 'password' => 'nueva contraseña']);

        $request->user()->update(['password' => $data['password']]);

        return to_route('profile.edit')->with('status', 'Contraseña actualizada correctamente.');
    }

    /** Crea un token personal para usar la API REST desde Postman/Insomnia. Se muestra una sola vez. */
    public function createToken(Request $request): RedirectResponse
    {
        $data = $request->validate(['token_name' => ['required', 'string', 'max:60']], [], ['token_name' => 'nombre del token']);
        $token = $request->user()->createToken($data['token_name'])->plainTextToken;

        return to_route('profile.edit')->with('new_token', $token)->with('status', 'Token creado. Cópielo ahora: no volverá a mostrarse.');
    }

    public function deleteToken(Request $request, int $token): RedirectResponse
    {
        $request->user()->tokens()->whereKey($token)->delete();

        return to_route('profile.edit')->with('status', 'Token revocado.');
    }
}
