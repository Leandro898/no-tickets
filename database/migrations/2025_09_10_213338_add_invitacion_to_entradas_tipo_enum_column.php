<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE entradas MODIFY tipo ENUM('digital', 'fisico', 'invitacion')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Para la función down, revertimos el cambio quitando 'invitacion'
        DB::statement("ALTER TABLE entradas MODIFY tipo ENUM('digital', 'fisico')");
    }
};
