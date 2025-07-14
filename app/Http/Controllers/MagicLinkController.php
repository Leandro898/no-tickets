<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\MagicLink;
use App\Models\User;

class MagicLinkController extends Controller
{
    public function login(Request $request)
    {
        $token = $request->query('token');

        $link = MagicLink::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $user = User::firstOrCreate(
            ['email' => $link->email],
            [
                'name'     => explode('@', $link->email)[0],
                'password' => bcrypt(Str::random(16)),
            ]
        );

        Auth::login($user);

        return redirect()->route('mis-entradas');
    }
}
