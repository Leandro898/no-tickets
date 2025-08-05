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
        Schema::table('eventos', function (Blueprint $table) {
<<<<<<< HEAD
            $table->string('slug')->unique()->after('nombre');
=======
            if (! Schema::hasColumn('eventos', 'slug')) {
                $table->string('slug')->after('nombre');
            }
>>>>>>> ajustes-seats
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('eventos', function (Blueprint $table) {
<<<<<<< HEAD
            $table->dropColumn('slug');
=======
            if (Schema::hasColumn('eventos', 'slug')) {
                $table->dropColumn('slug');
            }
>>>>>>> ajustes-seats
        });
    }
};
