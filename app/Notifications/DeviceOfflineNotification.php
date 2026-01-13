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
        $type = ucfirst($this->device->type); // Ej: Router, Switch
        return [
            'device_id' => $this->device->id,
            'name' => $this->device->name,
            'ip' => $this->device->ip_address,
            'type' => $this->device->type,
            'message' => "El {$type} '{$this->device->name}' ({$this->device->ip_address}) está OFFLINE.",
            'time' => now()->toDateTimeString(),
        ];
    }
}