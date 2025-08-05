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
            $table->string('prefix')->nullable()->after('row');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('seats', function (Blueprint $table) {
            $table->dropColumn('prefix');
        });
    }
};
