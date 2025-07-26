<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('seats', function (Blueprint $table) {
            // cambia 'row' de INT a VARCHAR(10)
            $table->string('row', 10)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('seats', function (Blueprint $table) {
            // vuelve a INT si necesitas rollback
            $table->integer('row')->nullable()->change();
        });
    }
};
