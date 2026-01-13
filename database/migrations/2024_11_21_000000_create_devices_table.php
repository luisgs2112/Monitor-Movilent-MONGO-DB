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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            // Relación: Un dispositivo pertenece a una oficina
            $table->foreignId('office_id')->constrained()->onDelete('cascade');
            
            $table->string('name');
            $table->string('ip_address');
            $table->string('type')->default('router');
            
            // Campos para SNMP (Monitoreo)
            $table->integer('snmp_port')->default(161);
            $table->string('snmp_community')->default('public');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};