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
        Schema::create('seats', function (Blueprint $table) {
            $table->id();

            // Relación con evento
            $table->foreignId('evento_id')
                ->constrained('eventos')
                ->cascadeOnDelete();

            // Relación con tipo de entrada
            $table->foreignId('entrada_id')
                ->constrained('entradas')
                ->cascadeOnDelete();

            // Fila y número de asiento
            $table->unsignedInteger('row');
            $table->unsignedInteger('number');

            // Coordenadas (por ejemplo para renderizado en el canvas)
            $table->float('x');
            $table->float('y');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
