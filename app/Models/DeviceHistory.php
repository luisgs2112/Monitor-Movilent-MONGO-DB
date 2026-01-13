<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceHistory extends Model
{
    protected $fillable = ['device_id', 'status', 'latency', 'cpu_usage'];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}