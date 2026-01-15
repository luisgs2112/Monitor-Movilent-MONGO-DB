<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'name',
        'ip_address',
        'type',
        'snmp_port',
        'snmp_community',
        'is_active',
        'status',
        'uptime',
        'os_info',
        'last_checked_at',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'snmp_port' => 161,
        'snmp_community' => 'public',
        'type' => 'router',
        'is_active' => true,
        'status' => 'unknown',
    ];

    
    
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Asegurar que siempre haya una comunidad SNMP (fallback a 'public')
     */
    public function getSnmpCommunityAttribute($value)
    {
        return $value ?: 'public';
    }

    
    
    public function histories()
    {
        return $this->hasMany(DeviceHistory::class);
    }

    
    
    public function isOnline()
    {
        return $this->status === 'online';
    }

    
    
    public function ping(): bool
    {
        $output = [];
        $returnVar = 0;
        $ip = $this->ip_address;

        
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            
            
            $cmd = "ping -n 1 -w 1000 " . escapeshellarg($ip);
            exec($cmd, $output, $returnVar);

            
            
            foreach ($output as $line) {
                if (stripos($line, 'TTL=') !== false) return true;
            }
            return false;
        } else {
            
            
            $cmd = "ping -c 1 -W 1 " . escapeshellarg($ip);
            exec($cmd, $output, $returnVar);
            return $returnVar === 0;
        }
    }

    
    
    public function getSnmpUptime()
    {
        if (!extension_loaded('snmp')) return null;

        // Configuración robusta para Windows: Sin MIBs y valores planos
        putenv('MIBS=NONE');
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
        snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);

        try {
            // Timeout aumentado a 2s (2000000 us) y 3 reintentos
            $output = @snmp2_get($this->ip_address, $this->snmp_community, '.1.3.6.1.2.1.1.3.0', 2000000, 3);
            
            if ($output !== false) {
                // El valor viene en centésimas de segundo (TimeTicks)
                $ticks = (int) $output;
                $days = floor($ticks / (100 * 60 * 60 * 24));
                $hours = floor(($ticks / (100 * 60 * 60)) % 24);
                $minutes = floor(($ticks / (100 * 60)) % 60);
                $seconds = floor(($ticks / 100) % 60);
                
                $timeStr = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                
                return ($days > 0) ? "{$days}d {$timeStr}" : $timeStr;
            }
            return null; 
        } catch (\Throwable $e) {
            return null;
        }
    }

    
    
    public function getSnmpOs()
    {
        if (!extension_loaded('snmp')) return null;

        putenv('MIBS=NONE');
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
        snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);

        try {
            $output = @snmp2_get($this->ip_address, $this->snmp_community, '.1.3.6.1.2.1.1.1.0', 2000000, 3);
            
            if ($output !== false) {
                return trim(str_replace('"', '', $output));
            }
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    
    
    public function getSnmpCpuUsage()
    {
        if (!extension_loaded('snmp')) return null;

        putenv('MIBS=NONE');
        snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
        snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);

        try {
            $output = @snmp2_walk($this->ip_address, $this->snmp_community, '.1.3.6.1.2.1.25.3.3.1.2', 2000000, 3);

            if ($output && is_array($output) && count($output) > 0) {
                $total = 0;
                $count = 0;
                foreach ($output as $line) {
                    $total += (int) $line;
                    $count++;
                }
                return $count > 0 ? round($total / $count, 2) : 0;
            }
        } catch (\Throwable $e) {
            return null;
        }
        return null;
    }

    
    
    public function getOsIconAttribute()
    {
        if (!$this->os_info) return 'fas fa-desktop'; 

        $os = strtolower($this->os_info);

        if (str_contains($os, 'windows')) return 'fab fa-windows';
        if (str_contains($os, 'linux')) return 'fab fa-linux';
        if (str_contains($os, 'cisco') || str_contains($os, 'router')) return 'fas fa-network-wired';
        if (str_contains($os, 'darwin') || str_contains($os, 'mac')) return 'fab fa-apple';
        
        return 'fas fa-desktop';
    }

    /**
     * Convierte el string de uptime (ej: "2d 05:23:10") a segundos totales
     */
    public function getUptimeSecondsAttribute()
    {
        if (!$this->uptime) return 0;

        // Extraer días, horas, minutos y segundos usando Expresiones Regulares
        if (preg_match('/(?:(\d+)d\s)?(\d+):(\d+):(\d+)/', $this->uptime, $matches)) {
            $days = isset($matches[1]) ? (int)$matches[1] : 0;
            $hours = (int)$matches[2];
            $minutes = (int)$matches[3];
            $seconds = (int)$matches[4];

            return ($days * 86400) + ($hours * 3600) + ($minutes * 60) + $seconds;
        }

        return 0;
    }
}