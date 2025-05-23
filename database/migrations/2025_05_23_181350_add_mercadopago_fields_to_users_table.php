<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // mp_access_token: El token de acceso del vendedor para realizar operaciones en su nombre.
            $table->string('mp_access_token', 255)->nullable()->after('remember_token');
            // mp_refresh_token: Usado para obtener un nuevo access_token cuando el actual expira.
            $table->string('mp_refresh_token', 255)->nullable()->after('mp_access_token');
            // mp_public_key: La clave pública del vendedor (puede ser útil para el frontend si el vendedor usa un checkout propio).
            $table->string('mp_public_key', 255)->nullable()->after('mp_refresh_token');
            // mp_user_id: El ID de usuario de Mercado Pago del vendedor. CRUCIAL para el split de pagos (sponsor_id).
            $table->bigInteger('mp_user_id')->nullable()->after('mp_public_key');
            // mp_expires_in: Fecha y hora en que expira el mp_access_token.
            $table->timestamp('mp_expires_in')->nullable()->after('mp_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mp_access_token',
                'mp_refresh_token',
                'mp_public_key',
                'mp_user_id',
                'mp_expires_in',
            ]);
        });
    }
};
