<?php

use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required', 
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Credenciales incorrectas'], 401);
    }

    
    return response()->json([
        'token' => $user->createToken($request->device_name)->plainTextToken,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role, 
        ]
    ]);
});


Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/devices', function () {
        $devices = Device::with('office:id,name')->get()->map(function ($device) {
            return [
                'id' => $device->id,
                'name' => $device->name,
                'ip' => $device->ip_address,
                'status' => $device->status,
                'cpu' => $device->histories()->latest()->first()->cpu_usage ?? 0,
                'uptime' => $device->uptime ?? 'N/A',
                'office' => $device->office->name ?? 'Sin Oficina',
                'last_checked' => $device->last_checked_at ? $device->last_checked_at->diffForHumans() : 'Nunca',
                'icon' => $device->os_icon,
            ];
        });
        return response()->json($devices);
    });

    
    Route::get('/devices/{id}', function ($id) {
        $device = Device::with('office:id,name')->findOrFail($id);

        
        $history = $device->histories()
            ->where('created_at', '>=', now()->subHours(24))
            ->oldest()
            ->get()
            ->map(function ($h) {
                return [
                    'time' => $h->created_at->format('H:i'),
                    'status' => $h->status === 'online' ? 1 : 0,
                    'cpu' => $h->cpu_usage ?? 0,
                ];
            });

        return response()->json([
            'info' => [
                'id' => $device->id,
                'name' => $device->name,
                'ip' => $device->ip_address,
                'office' => $device->office->name ?? 'Sin Oficina',
                'status' => $device->status,
                'uptime' => $device->uptime ?? 'N/A',
                'last_checked' => $device->last_checked_at ? $device->last_checked_at->diffForHumans() : 'Nunca',
                'icon' => $device->os_icon,
            ],
            'history' => $history
        ]);
    });

    Route::get('/notifications', function () {
        $notifications = \Illuminate\Support\Facades\DB::table('notifications')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($n) {
                $data = json_decode($n->data, true);
                $isCpu = str_contains($n->type, 'HighCpu');
                return [
                    'id' => $n->id,
                    'type' => $isCpu ? 'cpu' : 'offline',
                    'title' => $isCpu ? 'CPU Crítico' : 'Dispositivo Offline',
                    'message' => $data['message'] ?? 'Alerta del sistema',
                    'time' => \Carbon\Carbon::parse($n->created_at)->diffForHumans(),
                    'created_at' => $n->created_at, 
                ];
            });
        return response()->json($notifications);
    });

    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    });
});
