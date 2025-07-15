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
        // 1️⃣ Validar datos
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        // 2️⃣ Crear usuario (password dummy para no romper NOT NULL)
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'     => $data['name'],
                'password' => Hash::make(Str::random(40)),
            ]
        );

        // 3️⃣ (Opcional) disparar evento Registered
        event(new Registered($user));

        // 4️⃣ Generar y guardar el Magic Link en BD
        $token = Str::random(64);
        MagicLink::create([
            'email'      => $user->email,
            'token'      => $token,
            'expires_at' => now()->addHours(2),
        ]);

        // 5️⃣ Enviar la notificación que creará el enlace firmado y el email
        $user->notify(new MagicLinkLogin($token));

        // 6️⃣ Redirigir a “Revisa tu correo” pasando el email en session
        return redirect()
            ->route('auth.check-email')
            ->with('email_to_verify', $user->email);
    }
}
