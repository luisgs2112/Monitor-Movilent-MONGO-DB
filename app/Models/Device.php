<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    
    
    public function office()
    {
        return $this->belongsTo(Office::class);
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
        
        
        if (!extension_loaded('snmp')) {
            return "ERROR: Extensión SNMP desactivada en PHP";
        }

        
        
        putenv('MIBS=NONE');
        
        
        putenv('MIBDIRS=' . str_replace('\\', '/', sys_get_temp_dir()));

        
        
        
        
        try {
            
            
            
            
            $output = @snmp2_get($this->ip_address, $this->snmp_community, '.1.3.6.1.2.1.1.3.0', 1000000, 2);
            
            if ($output !== false) {
                
                
                return str_replace('Timeticks: ', '', $output);
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
        putenv('MIBDIRS=' . str_replace('\\', '/', sys_get_temp_dir()));

        
        
        try {
            $output = @snmp2_get($this->ip_address, $this->snmp_community, '.1.3.6.1.2.1.1.1.0', 1000000, 2);
            
            if ($output !== false) {
                
                
                return str_replace(['STRING: ', '"'], '', $output);
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
        putenv('MIBDIRS=' . str_replace('\\', '/', sys_get_temp_dir()));

        try {
            
            
            
            
            $output = @snmp2_walk($this->ip_address, $this->snmp_community, '.1.3.6.1.2.1.25.3.3.1.2', 1000000, 2);

            if ($output && is_array($output) && count($output) > 0) {
                $total = 0;
                $count = 0;
                foreach ($output as $line) {
                    
                    
                    $value = (int) str_replace('INTEGER: ', '', $line);
                    $total += $value;
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
}