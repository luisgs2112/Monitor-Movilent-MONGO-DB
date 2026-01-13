<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DeviceController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Device::class, 'device');
    }

    public function index(Request $request)
    {
        
        $query = Device::with('office')->latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $request->search . '%');
            });
        }

        
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        $devices = $query->get();
        $offices = Office::orderBy('name')->get(); 
        return view('devices.index', compact('devices', 'offices'));
    }

    public function create()
    {
        $offices = Office::all();
        return view('devices.create', compact('offices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'office_id' => 'required|exists:offices,id',
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ipv4|unique:devices',
            'type' => 'required|string|in:router,switch,server,other',
            'snmp_port' => 'required|integer|min:1|max:65535',
            'snmp_community' => 'required|string|max:255',
        ]);

        $device = new Device($validated);
        $device->is_active = $request->has('is_active');
        
        
        if ($device->is_active) {
            $device->status = $device->ping() ? 'online' : 'offline';
            $device->last_checked_at = now();
        }
        
        $device->save();

        return redirect()->route('devices.index')->with('success', 'Dispositivo registrado exitosamente.');
    }

    public function show(Device $device)
    {
        
        
        $rawHistory = $device->histories()
            ->where('created_at', '>=', now()->subDays(7))
            ->oldest()
            ->get();

        
        
        $compressedHistory = collect();
        $lastStatus = null;

        foreach ($rawHistory as $index => $record) {
            
            $isFirst = $index === 0;
            $isLast = $index === $rawHistory->count() - 1;
            $statusChanged = $lastStatus !== $record->status;

            if ($isFirst || $isLast || $statusChanged) {
                $compressedHistory->push($record);
            }
            $lastStatus = $record->status;
        }

        
        
        $labels = $compressedHistory->map(fn($h) => $h->created_at->format('d/m H:i'));
        
        $statuses = $compressedHistory->map(fn($h) => $h->status === 'online' ? 1 : 0);

        
        $cpuHistory = $device->histories()
            ->whereNotNull('cpu_usage')
            ->where('created_at', '>=', now()->subHours(24))
            ->oldest()
            ->get();

        $cpuLabels = $cpuHistory->map(fn($h) => $h->created_at->format('H:i'));
        $cpuValues = $cpuHistory->map(fn($h) => $h->cpu_usage);

        return view('devices.show', compact('device', 'labels', 'statuses', 'cpuLabels', 'cpuValues'));
    }

    public function edit(Device $device)
    {
        $offices = Office::all();
        return view('devices.edit', compact('device', 'offices'));
    }

    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'office_id' => 'required|exists:offices,id',
            'name' => 'required|string|max:255',
            'ip_address' => ['required', 'ipv4', Rule::unique('devices')->ignore($device->id)],
            'type' => 'required|string|in:router,switch,server,other',
            'snmp_port' => 'required|integer|min:1|max:65535',
            'snmp_community' => 'required|string|max:255',
        ]);

        $device->fill($validated);
        $device->is_active = $request->has('is_active');
        
        
        if ($device->is_active) {
            $device->status = $device->ping() ? 'online' : 'offline';
            $device->last_checked_at = now();
        }
        
        $device->save();

        return redirect()->route('devices.index')->with('success', 'Dispositivo actualizado exitosamente.');
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('devices.index')->with('success', 'Dispositivo eliminado exitosamente.');
    }
}
