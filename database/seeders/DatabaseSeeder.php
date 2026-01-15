<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Office;
use App\Models\Device;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear Usuario Admin (Usamos firstOrCreate para evitar duplicados)
        User::firstOrCreate(
            ['email' => 'admin@monitor.com'], // Busca por este campo
            [
                'name' => 'Administrador',
                'role' => 'admin',
                'phone' => '04161234567',
                'password' => 'password', // El modelo User ya tiene cast 'hashed', se encripta solo
                'is_active' => true,
            ]
        );

        // 2. Crear una Oficina de prueba
        $office = Office::firstOrCreate(
            ['name' => 'Oficina Principal'],
            [
                'branch_code' => 'CCS-001',
                'city' => 'Caracas',
                'address' => 'Sede Central',
            ]
        );

        // 3. Crear un Dispositivo de prueba (Localhost) vinculado a la oficina
        Device::firstOrCreate(
            ['ip_address' => '127.0.0.1'],
            [
                'name' => 'Servidor Local',
                'office_id' => $office->id,
                'type' => 'server',
                'status' => 'online',
                'is_active' => true,
            ]
        );
    }
}
