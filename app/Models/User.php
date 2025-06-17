<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Mantén esta línea comentada si no la usas
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// La línea "use Laravel\Sanctum\HasApiTokens;" debe estar comentada o eliminada

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // ¡IMPORTANTE!: Quita 'HasApiTokens' de esta línea si no vas a usar Sanctum
    use HasFactory, Notifiable; 

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mp_access_token',      // <--- AÑADIDO: Token de acceso de Mercado Pago del vendedor
        'mp_refresh_token',     // <--- AÑADIDO: Token de refresco de Mercado Pago del vendedor
        'mp_public_key',        // <--- AÑADIDO: Clave pública de Mercado Pago del vendedor
        'mp_user_id',           // <--- AÑADIDO: ID de usuario de Mercado Pago del vendedor
        'mp_expires_in',        // <--- AÑADIDO: Fecha de expiración del token de acceso
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mp_access_token',      // <--- AÑADIDO: Ocultar por seguridad
        'mp_refresh_token',     // <--- AÑADIDO: Ocultar por seguridad
        'mp_public_key',        // <--- AÑADIDO: Ocultar por seguridad
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mp_expires_in' => 'datetime', // <--- AÑADIDO: Castear como datetime
        ];
    }

    /**
     * Helper para verificar si el usuario tiene una cuenta de Mercado Pago conectada.
     * @return bool
     */
    public function hasMercadoPagoAccount(): bool
    {
        return $this->mp_access_token && $this->mp_refresh_token && $this->mp_user_id && $this->mp_expires_in;
    }

    /**
     * Helper para obtener el access token de Mercado Pago del vendedor.
     * @return string|null
     */
    public function getMercadoPagoAccessToken(): ?string
    {
        // En una aplicación real, aquí deberías implementar la lógica para
        // refrescar el token si 'mp_expires_in' está en el pasado.
        // Por ahora, simplemente devolvemos el token almacenado.
        return $this->mp_access_token;
    }

    /**
     * Helper para obtener la public key de Mercado Pago del vendedor.
     * @return string|null
     */
    public function getMercadoPagoPublicKey(): ?string
    {
        return $this->mp_public_key;
    }
}
