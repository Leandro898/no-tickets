<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('entradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->integer('stock_inicial');
            $table->integer('stock_actual');
            $table->integer('max_por_compra')->default(1);
            $table->decimal('precio', 10, 2);
            $table->dateTime('disponible_desde')->nullable();
            $table->dateTime('disponible_hasta')->nullable();
            $table->enum('tipo', ['digital', 'fisico'])->default('digital');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('entradas');
    }
};
