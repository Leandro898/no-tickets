<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Añadimos la columna 'items_data' de tipo JSON (o TEXT si tu DB no lo soporta bien)
            // Se ubica después de 'total_amount'
            $table->json('items_data')->nullable()->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('items_data');
        });
    }
};