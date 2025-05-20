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
        Schema::table('entradas', function (Blueprint $table) {
            $table->boolean('visible')->default(true)->after('tipo'); // Asegúrate de ponerlo después de 'tipo' o el nombre de la columna donde quieres que aparezca
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entradas', function (Blueprint $table) {
            $table->dropColumn('visible');
        });
    }
};
