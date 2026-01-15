<?php

use App\Models\Device;
use App\Models\Office;
use App\Models\User;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $officesCount = Office::count();
    $usersCount = Auth::user()->role === 'admin' ? User::count() : 0;
    $devicesCount = Device::count();
    
    
    $onlineCount = Device::where('status', 'online')->count();
    $offlineCount = Device::where('status', 'offline')->count();
    $unknownCount = Device::where('status', 'unknown')->count();

    
    $offlineDevices = Device::with('office')->where('status', 'offline')->get();

    // Obtener notificaciones paginadas para la vista
    $notifications = Auth::user()->notifications()->paginate(10);

    return view('dashboard', compact('officesCount', 'usersCount', 'devicesCount', 'onlineCount', 'offlineCount', 'unknownCount', 'offlineDevices', 'notifications'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    
    Route::resource('offices', OfficeController::class);

    
    Route::resource('devices', DeviceController::class);

    
    Route::resource('users', UserController::class);

    
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    
    Route::get('/monitor/check-alerts', function () {
        return auth()->check() 
            ? auth()->user()->unreadNotifications->where('type', 'App\Notifications\DeviceHighCpuNotification')->values() 
            : [];
    });
});

require __DIR__.'/auth.php';
