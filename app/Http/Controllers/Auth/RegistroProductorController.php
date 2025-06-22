<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;

class RegistroProductorController extends Controller
{
    // METODOS PARA EMAIL
    public function showEmailForm()
    {
        return view('auth.productor.email');
    }

    public function handleEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        session(['registro_email' => $request->email]);

        return redirect()->route('registro.password');
    }

    // METODOS PARA CONTRASENIA
    public function showPasswordForm()
    {
        // Validamos que venga con email en sesión
        if (!session()->has('registro_email')) {
            return redirect()->route('registro.opciones');
        }

        return view('auth.productor.password');
    }

    public function handlePassword(Request $request)
    {
        if (!session()->has('registro_email')) {
            return redirect()->route('registro.opciones');
        }

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        session(['registro_password' => $request->password]);

        return redirect()->route('registro.verificacion'); // siguiente paso
    }

    //METODOS PARA VALIDAR EMAIL
    public function showVerificationForm()
    {
        if (!session()->has('registro_email') || !session()->has('registro_password')) {
            return redirect()->route('registro.opciones');
        }

        // Generar y guardar código si no existe aún
        if (!session()->has('registro_codigo')) {
            $codigo = rand(100000, 999999);
            session(['registro_codigo' => $codigo]);

            // Enviar el código por email
            Mail::raw("Tu código de verificación es: {$codigo}", function ($message) {
                $message->to(session('registro_email'))
                        ->subject('Código de verificación');
            });
        }

        return view('auth.productor.codigo');
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'codigo' => 'required|array|size:6',
            'codigo.*' => 'required|digits:1',
        ]);

        $codigoIngresado = implode('', $request->codigo);

        if ($codigoIngresado != session('registro_codigo')) {
            return back()->withErrors(['codigo' => 'El código ingresado es incorrecto.']);
        }

        // Crear usuario
        $user = User::create([
            'email' => session('registro_email'),
            'password' => bcrypt(session('registro_password')),
            'name' => 'Productor',
            'role' => 'vendedor',
            'email_verified_at' => now(),
        ]);

        auth()->login($user);

        // Limpiar la sesión temporal
        session()->forget(['registro_email', 'registro_password', 'registro_codigo']);

        return redirect('/admin/eventos');
    }


    //METODOS PARA REENVIO DE CODIGO PARA CONFIRMAR CREACION DE CUENTA POR EMAIL
    public function reenviarCodigo(Request $request)
    {
        if (!session()->has('registro_email')) {
            return redirect()->route('registro.opciones');
        }

        $codigo = rand(100000, 999999);
        session([
            'registro_codigo' => $codigo,
            'registro_codigo_timestamp' => now()->timestamp, // para el contador
        ]);

        Mail::raw("Tu nuevo código de verificación es: {$codigo}", function ($message) {
            $message->to(session('registro_email'))
                    ->subject('Nuevo código de verificación');
        });

        return back()->with('reenviado', true);
    }

}
