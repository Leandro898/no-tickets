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
        Schema::table('eventos', function (Blueprint $table) {
            // Verifica si la columna 'estado' ya existe y luego modifícala.
            // Si tu 'estado' ya es un string/varchar, puedes omitir el change().
            // Si es un boolean o enum restrictivo, necesitas cambiarlo a string.
            $table->string('estado')->default('active')->change(); // Cambia el tipo a string y establece un default

            // Si tienes valores existentes como TRUE/FALSE o 1/0, considera migrarlos:
            // DB::table('eventos')
            //     ->where('estado', true) // o 1
            //     ->update(['estado' => 'active']);
            // DB::table('eventos')
            //     ->where('estado', false) // o 0
            //     ->update(['estado' => 'inactive']); // Si tienes un estado 'inactive'
            // También puedes definir 'suspended' o 'cancelled' si aplica a viejos datos.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            // Define cómo revertir el cambio.
            // Esto dependerá del tipo original de tu columna 'estado'.
            // Por ejemplo, si era un boolean:
            // $table->boolean('estado')->default(true)->change();
        });
    }
};