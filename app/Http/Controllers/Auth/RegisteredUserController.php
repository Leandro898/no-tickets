<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MagicLink;
use App\Notifications\MagicLinkLogin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Mostrar el formulario de registro.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Procesar una petición de registro.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'  => ['required', 'in:cliente,productor'],
            'telefono' => ['nullable', 'string', 'max:30'], // si quieres que sea opcional
        ]);

        // Crear usuario
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'     => $data['name'],
                'password' => Hash::make(Str::random(40)),
                'telefono' => $data['telefono'] ?? null,
            ]
        );

        // Asignar rol Spatie
        $user->syncRoles([$data['role']]);

        // Disparar evento Registered
        event(new Registered($user));

        // Generar Magic Link y notificar
        $token = Str::random(64);
        MagicLink::create([
            'email'      => $user->email,
            'token'      => $token,
            'expires_at' => now()->addHours(2),
        ]);
        $user->notify(new MagicLinkLogin($token));

        return redirect()
            ->route('auth.check-email')
            ->with('email_to_verify', $user->email);
    }
}
