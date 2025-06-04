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
        Schema::table('purchased_tickets', function (Blueprint $table) {
            // Agrega la columna scan_date como un timestamp anulable (nullable)
            // Usamos nullable para que no dé error en tickets ya existentes que no la tengan
            $table->timestamp('scan_date')->nullable()->after('status'); // Puedes ajustar 'after' a la columna que quieras
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchased_tickets', function (Blueprint $table) {
            // Para revertir la migración, simplemente elimina la columna
            $table->dropColumn('scan_date');
        });
    }
};