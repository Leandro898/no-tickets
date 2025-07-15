<?php

namespace App\Http\Controllers;

use App\Models\MagicLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MagicLinkController extends Controller
{
    /**
     * 1) Click en el Magic Link: valida firma, loguea y manda a setup-password si no hay pass.
     */
    public function login(Request $request, User $user)
    {
        // (opcional) Si usas tu propia tabla MagicLink, puedes verificar token:
        // $token = $request->query('token');
        // $link = MagicLink::where('token',$token)->where('email',$user->email)->firstOrFail();
        // if (Carbon::parse($link->expires_at)->isPast()) abort(403,'Enlace expirado');
        // $link->delete();

        // marca email como verificado si aplica
        if (method_exists($user, 'markEmailAsVerified') && ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // loguea
        Auth::login($user, true);

        // si no tiene contraseña (null o hash random), redirige a setup
        if (empty($user->password) || ! Hash::needsRehash($user->password)) {
            return redirect()->route('password.setup');
        }

        // REDIRECCIÓN SEGÚN ROL
        if ($user->hasRole('productor')) {
            // Ajusta la ruta según tu configuración Filament
            return redirect()->intended('/admin/eventos');
        }

        // Si es cliente o cualquier otro rol, redirige al home/front
        return redirect()->route('mis-entradas');
    }

    /**
     * 2) Mostrar form para crear contraseña
     */
    public function showSetupPassword()
    {
        return view('auth.passwords.setup');
    }

    /**
     * 3) Procesar nuevo password
     */
    public function setupPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        // Redirecciona según el rol
        if ($user->hasAnyRole(['admin', 'productor'])) {
            return redirect()->intended('/admin/eventos')
                ->with('success', '¡Tu contraseña ha sido creada!');
        }

        return redirect()->route('mis-entradas')
            ->with('success', '¡Tu contraseña ha sido creada!');
    }
}
