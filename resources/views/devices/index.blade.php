<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Gestión de Dispositivos') }}
            </h2>
            
            <!-- Indicador de Estado en Vivo -->
            <div class="ml-4 flex items-center text-xs text-gray-500 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">
                <span class="relative flex h-2 w-2 mr-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <span id="live-status">Monitoreando...</span>
            </div>

            <!-- Botón para desbloquear audio (Necesario para navegadores modernos) -->
            <button id="btn-enable-audio" class="ml-3 flex items-center text-xs bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 px-3 py-1 rounded-full transition-colors cursor-pointer">
                🔇 Activar Sonido
            </button>

            <!-- Buscador -->
            <form method="GET" action="{{ route('devices.index') }}" class="flex items-center ml-4">
                <!-- Filtro de Oficina -->
                <select name="office_id" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm mr-2" onchange="this.form.submit()">
                    <option value="">Todas las Oficinas</option>
                    @foreach($offices as $office)
                        <option value="{{ $office->id }}" {{ request('office_id') == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                    @endforeach
                </select>

                <input type="text" name="search" placeholder="Buscar por IP o nombre..." value="{{ request('search') }}" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                <button type="submit" class="ml-2 bg-gray-800 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">Buscar</button>
            </form>

            @can('create', App\Models\Device::class)
            <a href="{{ route('devices.create') }}" class="bg-orange-600 hover:bg-orange-700 text-black font-bold py-2 px-4 rounded">
                Nuevo Dispositivo
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div id="devices-table" class="overflow-x-auto">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Nombre / IP</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Tipo</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Oficina</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Estado</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Uptime</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($devices as $device)
                                <tr>
                                    <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                                        <div class="font-semibold">
                                            <!-- Icono del OS con Tooltip -->
                                            <span title="{{ $device->os_info }}" class="cursor-help mr-1 text-lg">
                                                <i class="{{ $device->os_icon }}"></i>
                                            </span>
                                            <a href="{{ route('devices.show', $device) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 hover:underline">
                                                {{ $device->name }}
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-300">{{ $device->ip_address }}</div>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                            {{ ucfirst($device->type) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white">{{ $device->office->name }}</td>
                                    <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                                        @if($device->is_active)
                                            @if($device->status === 'online')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                                    ● Online
                                                </span>
                                            @elseif($device->status === 'offline')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                                    ● Offline
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                                    ● Pendiente
                                                </span>
                                            @endif
                                            <div class="text-xs text-gray-400 mt-1">
                                                {{ $device->last_checked_at ? $device->last_checked_at->diffForHumans() : 'Nunca' }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">Monitoreo desactivado</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                                        @if($device->uptime && $device->last_checked_at)
                                            @php
                                                
                                                $currentUptime = $device->uptime_seconds + now()->diffInSeconds($device->last_checked_at);

                                                
                                                $d = floor($currentUptime / 86400);
                                                $h = floor(($currentUptime % 86400) / 3600);
                                                $m = floor(($currentUptime % 3600) / 60);
                                                $s = $currentUptime % 60;
                                                $formatted = sprintf('%02d:%02d:%02d', $h, $m, $s);
                                                $displayUptime = $d > 0 ? "{$d}d {$formatted}" : $formatted;
                                            @endphp
                                            <span class="live-uptime text-gray-700 dark:text-white font-mono text-xs" data-seconds="{{ $currentUptime }}">
                                                {{ $displayUptime }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                                        <div class="flex items-center gap-2">
                                            @can('update', $device)
                                            <a href="{{ route('devices.edit', $device) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">Editar</a>
                                            @endcan
                                            
                                            @can('delete', $device)
                                            <form action="{{ route('devices.destroy', $device) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este dispositivo?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Eliminar</button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-center text-gray-500 dark:text-gray-400">
                                        No hay dispositivos registrados.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const startTicker = () => {
                
                if (window.uptimeInterval) clearInterval(window.uptimeInterval);

                window.uptimeInterval = setInterval(() => {
                    document.querySelectorAll('.live-uptime').forEach(el => {
                        let totalSeconds = parseInt(el.getAttribute('data-seconds'));
                        
                        
                        if (isNaN(totalSeconds)) return;
                        
                        totalSeconds++;
                        el.setAttribute('data-seconds', totalSeconds);

                        const days = Math.floor(totalSeconds / 86400);
                        const hours = Math.floor((totalSeconds % 86400) / 3600);
                        const minutes = Math.floor((totalSeconds % 3600) / 60);
                        const seconds = totalSeconds % 60;

                        const timeStr = [hours, minutes, seconds]
                            .map(v => v.toString().padStart(2, '0'))
                            .join(':');
                        
                        el.innerText = days > 0 ? `${days}d ${timeStr}` : timeStr;
                    });
                }, 1000);
            };

            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', startTicker);
            } else {
                startTicker();
            }
        })();
    </script>
</x-app-layout>