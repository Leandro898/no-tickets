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
            $table->string('type')->after('entrada_id')->default('seat');
            $table->float('width')->nullable()->after('type');
            $table->float('height')->nullable()->after('width');
            $table->float('radius')->nullable()->after('height');
            $table->string('label')->nullable()->after('radius');
            $table->float('font_size')->nullable()->after('label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('seats', function (Blueprint $table) {
            $table->dropColumn(['type', 'width', 'height', 'radius', 'label', 'font_size']);
        });
    }
};
