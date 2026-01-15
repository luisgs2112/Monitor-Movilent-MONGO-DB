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
        Schema::table('devices', function (Blueprint $table) {
            // Estado del dispositivo
            // unknown = nunca revisado, online = en línea, offline = caído
            $table->string('status')->default('unknown')->comment('Estado actual: online, offline, unknown');
            $table->timestamp('last_checked_at')->nullable()->comment('Fecha y hora del último escaneo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['status', 'last_checked_at']);
        });
    }
};