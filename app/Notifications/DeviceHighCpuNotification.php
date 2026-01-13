<?php

namespace App\Notifications;

use App\Models\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeviceHighCpuNotification extends Notification
{
    use Queueable;

    protected $device;
    protected $cpuUsage;

    public function __construct(Device $device, $cpuUsage)
    {
        $this->device = $device;
        $this->cpuUsage = $cpuUsage;
    }

    public function via($notifiable)
    {
        // Puedes agregar 'mail' al array si tienes configurado el correo
        return ['database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->error()
                    ->subject('⚠️ CPU Crítico: ' . $this->device->name)
                    ->line('El dispositivo ' . $this->device->name . ' tiene una carga de CPU inusualmente alta.')
                    ->line('Uso actual: ' . $this->cpuUsage . '%')
                    ->action('Ver Dispositivo', route('devices.show', $this->device));
    }

    public function toArray($notifiable)
    {
        return [
            'device_id' => $this->device->id,
            'name' => $this->device->name,
            'ip' => $this->device->ip_address,
            'message' => "🔥 CPU Crítico ({$this->cpuUsage}%) en {$this->device->name}",
            'cpu_usage' => $this->cpuUsage,
        ];
    }
}