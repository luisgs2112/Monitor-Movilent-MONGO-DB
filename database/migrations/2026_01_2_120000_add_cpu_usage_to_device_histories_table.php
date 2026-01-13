<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('device_histories', function (Blueprint $table) {
            // Agregamos la columna para guardar el % de CPU (puede ser nulo si no es un servidor)
            $table->integer('cpu_usage')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('device_histories', function (Blueprint $table) {
            $table->dropColumn('cpu_usage');
        });
    }
};