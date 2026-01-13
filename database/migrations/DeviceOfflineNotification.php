<?php

namespace App\Notifications;

use App\Models\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeviceOfflineNotification extends Notification
{
    use Queueable;

    protected $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'device_id' => $this->device->id,
            'name' => $this->device->name,
            'ip' => $this->device->ip_address,
            'message' => "El dispositivo {$this->device->name} ({$this->device->ip_address}) ha perdido conexión.",
            'time' => now()->toDateTimeString(),
        ];
    }
}