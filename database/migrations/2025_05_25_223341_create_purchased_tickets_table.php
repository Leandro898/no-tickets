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
        Schema::create('purchased_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('entrada_id')->constrained('entradas')->onDelete('cascade');
            $table->uuid('unique_code')->unique(); // UUID para el QR
            $table->string('qr_path')->nullable(); // ¡AQUÍ ESTÁ LA COLUMNA QUE FALTABA!
            $table->string('status')->default('valid'); // valid, used, cancelled
            $table->timestamp('scanned_at')->nullable(); // Fecha y hora en que fue escaneada
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchased_tickets');
    }
};