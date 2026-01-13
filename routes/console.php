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
    $devices = Device::where('is_active', true)->get();

    $this->info("Iniciando monitoreo de {$devices->count()} dispositivos...");

    foreach ($devices as $device) {
        $this->output->write("Verificando {$device->name} ({$device->ip_address})... ");
        
        $previousStatus = $device->status;
        
        
        $isOnline = $device->ping();
        $status = $isOnline ? 'online' : 'offline';
        $uptime = null;
        $osInfo = null;
        $cpuUsage = null;

        
        if ($isOnline) {
            $uptime = $device->getSnmpUptime();
            
            
            if ($uptime) {
                $osInfo = $device->getSnmpOs();
                $cpuUsage = $device->getSnmpCpuUsage();
            }

        }

        
        $updateData = [
            'status' => $status,
            'last_checked_at' => now(),
            'uptime' => $uptime,
        ];

        
        if ($osInfo) {
            $updateData['os_info'] = $osInfo;
        }

        $device->update($updateData);

        
        DeviceHistory::create([
            'device_id' => $device->id,
            'status' => $status,
            'cpu_usage' => $cpuUsage,
            
        ]);

        
        if ($isOnline === false && $previousStatus === 'online') {
            $users = User::whereIn('role', ['admin', 'operator'])->get();
            Notification::send($users, new DeviceOfflineNotification($device));
            Log::warning("Dispositivo caído: {$device->name} ({$device->ip_address}) - Notificación enviada.");
        }

        
        if ($cpuUsage !== null && $cpuUsage >= 90) {
            
            
            $cacheKey = 'cpu_alert_sent_' . $device->id;

            if (!Cache::has($cacheKey)) {
                $users = User::whereIn('role', ['admin', 'operator'])->get();
                if ($users->count() > 0) {
                    Notification::send($users, new DeviceHighCpuNotification($device, $cpuUsage));
                    Log::critical("CPU Crítico detectado: {$device->name} ({$cpuUsage}%) - Notificación enviada.");
                    $this->error("   ¡ALERTA ENVIADA! Se notificó a {$users->count()} usuarios.");
                    Cache::put($cacheKey, true, 1800); 
                } else {
                    $this->warn("CPU Crítico, pero NO se encontraron usuarios 'admin' u 'operator'.");
                }
            } else {
                $this->comment(" Alerta omitida por caché (ya se envió hace menos de 30 min).");
            }
        }

        if ($isOnline) {
            
            $msg = "ONLINE";
            if ($uptime) $msg .= " | Uptime: $uptime";
            if ($cpuUsage !== null) $msg .= " | CPU: {$cpuUsage}%";
            
            
            if (!$uptime && $cpuUsage === null) {
                $msg .= " |SNMP: Sin acceso (Verificar configuración en el equipo)";
            }
            
            $this->info($msg);
        } else {
            $this->error("OFFLINE");
        }
    }

    $this->newLine();
    $this->info('Monitoreo finalizado.');
})->purpose('Verifica el estado de conectividad (Ping) de los dispositivos');


Schedule::command('monitor:check')->everyMinute();
