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
        // 1️⃣ Validar datos de formulario
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        // 2️⃣ Crear o recuperar usuario (con password dummy para no romper NOT NULL)
        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'name'     => $request->name,
                'password' => Hash::make(Str::random(40)),
            ]
        );

        // 3️⃣ Disparar evento Registered (opcional si usas email verification de Laravel)
        event(new Registered($user));

        // 4️⃣ Generar y guardar el Magic Link
        $token     = Str::random(64);
        $expiresAt = now()->addHours(2);

        MagicLink::create([
            'email'      => $user->email,
            'token'      => $token,
            'expires_at' => $expiresAt,
        ]);

        // 5️⃣ Enviar la notificación con el Magic Link
        $user->notify(new MagicLinkLogin($token));

        // 6️⃣ Guardar en sesión el email para mostrarlo en la vista
        session()->flash('email_to_verify', $user->email);

        // 7️⃣ Redirigir a la página pública “Revisa tu correo” sin loguear al usuario
        return redirect()->route('auth.check-email');
    }
}
