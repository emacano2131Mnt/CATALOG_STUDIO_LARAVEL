<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [], ['name' => 'nombre', 'email' => 'correo electrónico', 'password' => 'contraseña']);

        // El cast "hashed" del modelo User cifra la contraseña con bcrypt.
        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();

        return to_route('dashboard')->with('status', '¡Bienvenido a Catalog Studio, '.$user->name.'!');
    }
}
