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
            $table->boolean('restringir_edad')->default(false)->after('estado');
            $table->integer('edad_min_hombres')->nullable()->after('restringir_edad');
            $table->integer('edad_min_mujeres')->nullable()->after('edad_min_hombres');
            $table->boolean('requerir_dni')->default(false)->after('edad_min_mujeres');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn([
                'restringir_edad',
                'edad_min_hombres',
                'edad_min_mujeres',
                'requerir_dni',
            ]);
        });
    }
};
