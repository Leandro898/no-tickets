<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchased_tickets', function (Blueprint $table) {
            // Cambiamos de VARCHAR(8) a VARCHAR(5)
            $table->string('short_code', 5)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchased_tickets', function (Blueprint $table) {
            $table->string('short_code', 5)->nullable()->change();
        });
    }
};
