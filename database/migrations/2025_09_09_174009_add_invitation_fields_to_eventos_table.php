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
            $table->boolean('es_privado')->default(false)->after('requerir_dni');
            $table->string('password_invitacion')->nullable()->after('es_privado');
            $table->integer('cupo_invitaciones')->nullable()->after('password_invitacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn(['es_privado', 'password_invitacion', 'cupo_invitaciones']);
        });
    }
};
