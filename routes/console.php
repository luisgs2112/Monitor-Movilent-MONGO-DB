<?php

use App\Models\Device;
use App\Models\DeviceHistory;
use App\Models\User;
use App\Notifications\DeviceOfflineNotification;
use App\Notifications\DeviceHighCpuNotification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('monitor:check', function () {
    $this->info('Iniciando escaneo de dispositivos...');

    
    $devices = Device::where('is_active', true)->get();

    foreach ($devices as $device) {
        $this->comment("Escaneando: {$device->name} ({$device->ip_address})...");

        $previousStatus = $device->status;

        
        $isOnline = $device->ping();
        
        $status = $isOnline ? 'online' : 'offline';
        
        
        $latency = $isOnline ? rand(2, 15) : null;

        $cpuUsage = null;
        $uptime = null;
        $osInfo = null;

        
        if ($isOnline) {
            $cpuUsage = $device->getSnmpCpuUsage();
            $uptime = $device->getSnmpUptime();
            $osInfo = $device->getSnmpOs();
        }

        
        $device->update([
            'status' => $status,
            'uptime' => $uptime ?? $device->uptime,
            'os_info' => $osInfo ?? $device->os_info,
            'last_checked_at' => now(),
        ]);

        
        DeviceHistory::create([
            'device_id' => $device->id,
            'status' => $status,
            'latency' => $latency,
            'cpu_usage' => $cpuUsage,
        ]);

        

        
        if ($isOnline === false && $previousStatus === 'online') {
            $users = User::whereIn('role', ['admin', 'operator'])->get();
            Notification::send($users, new DeviceOfflineNotification($device));
            $this->error("  -> ¡ALERTA! Dispositivo Offline. Notificación enviada.");
        }

        
        if ($cpuUsage !== null && $cpuUsage >= 95) {
            $cacheKey = 'cpu_alert_' . $device->id;
            
            if (!Cache::has($cacheKey)) {
                $users = User::whereIn('role', ['admin', 'operator'])->get();
                Notification::send($users, new DeviceHighCpuNotification($device, $cpuUsage));
                $this->error("  -> ¡ALERTA! CPU Crítico ({$cpuUsage}%). Notificación enviada.");
                Cache::put($cacheKey, true, 1800); 
            }
        }

        
        if ($isOnline) {
            $snmpStatus = ($uptime || $cpuUsage !== null) ? "SNMP: OK" : "SNMP: Sin acceso";
            $cpuStr = $cpuUsage !== null ? "{$cpuUsage}%" : "N/A";
            $uptimeStr = $uptime !== null ? $uptime : "N/A";
            $this->info("   ✅ ONLINE | {$snmpStatus} | CPU: {$cpuStr} | Uptime: {$uptimeStr}");
        } else {
            $this->error("   ❌ OFFLINE");
        }
    }

    $this->info('Escaneo completado exitosamente.');
})->purpose('Escanea dispositivos activos (Ping/SNMP) y guarda historial.');


Schedule::command('monitor:check')->everyMinute();

Artisan::command('monitor:debug', function () {
    $this->info('--- Diagnóstico SNMP ---');
    
    // 1. Verificar Extensión
    if (!extension_loaded('snmp')) {
        $this->error('❌ La extensión php_snmp no está habilitada en php.ini');
        return;
    }
    $this->info('✅ Extensión SNMP cargada.');

    // 2. Buscar dispositivo local
    $device = Device::where('ip_address', '127.0.0.1')->first();
    if (!$device) {
        $this->error('❌ No encontré el dispositivo 127.0.0.1 en la base de datos.');
        return;
    }
    
    $community = $device->snmp_community; // Usará el accessor que acabamos de crear
    $this->info("ℹ️ Objetivo: {$device->ip_address} | Comunidad: '{$community}'");

    // 3. Prueba SNMP Real
    $this->info('--- Probando conexión SNMP (Mostrando errores reales) ---');
    
    putenv('MIBS=NONE');
    snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
    snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);

    try {
        // OID de Descripción del Sistema (.1.3.6.1.2.1.1.1.0)
        // Quitamos el @ para ver el error si falla
        $result = snmp2_get($device->ip_address, $community, '.1.3.6.1.2.1.1.1.0', 2000000, 2);
        
        $this->info("✅ ÉXITO SNMP: " . $result);
    } catch (\Throwable $e) {
        $this->error("❌ ERROR SNMP: " . $e->getMessage());
        $this->line("Sugerencias si es Timeout:");
        $this->line("1. Verifica en 'Servicios' de Windows que 'SNMP Service' esté corriendo.");
        $this->line("2. En Propiedades > Seguridad: Asegúrate que 'public' esté en la lista (READ ONLY).");
        $this->line("3. En Propiedades > Seguridad: Asegúrate que 'Aceptar paquetes de estos hosts' incluya 127.0.0.1 o esté en 'Cualquier host'.");
    }
});
