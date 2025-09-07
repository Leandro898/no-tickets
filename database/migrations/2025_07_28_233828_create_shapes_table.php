<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shapes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')
                ->constrained('eventos')
                ->cascadeOnDelete();
            $table->string('type');      // 'rect', 'circle' o 'text'
            $table->float('x');
            $table->float('y');
            $table->float('width')->nullable();
            $table->float('height')->nullable();
            $table->integer('rotation')->default(0);
            $table->string('label')->nullable();
            $table->integer('font_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shapes');
    }
};
