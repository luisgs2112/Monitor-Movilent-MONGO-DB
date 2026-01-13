<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('Panel de Control') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Tarjetas de Resumen -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Oficinas -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="text-gray-500 dark:text-gray-200 text-sm font-medium uppercase">Oficinas Registradas</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $officesCount }}</div>
                </div>

                <!-- Dispositivos -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-indigo-500">
                    <div class="text-gray-500 dark:text-gray-200 text-sm font-medium uppercase">Dispositivos Totales</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $devicesCount }}</div>
                </div>

                <!-- Usuarios (Solo Admin) -->
                @if(Auth::user()->role === 'admin')
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <div class="text-gray-500 dark:text-gray-200 text-sm font-medium uppercase">Usuarios del Sistema</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $usersCount }}</div>
                </div>
                @endif
            </div>

            <!-- Sección del Gráfico -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4 text-gray-700 dark:text-gray-300">Estado de la Red en Tiempo Real</h3>
                    
                    <div class="flex flex-col md:flex-row items-center justify-center gap-8">
                        <!-- Canvas del Gráfico -->
                        <div class="relative h-64 w-64">
                            <canvas id="statusChart"></canvas>
                        </div>

                        <!-- Leyenda Personalizada -->
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <span class="w-4 h-4 rounded-full bg-green-500 mr-2"></span>
                                <span class="text-gray-600 dark:text-gray-300">Online: <strong>{{ $onlineCount }}</strong></span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-4 h-4 rounded-full bg-red-500 mr-2"></span>
                                <span class="text-gray-600 dark:text-gray-300">Offline: <strong>{{ $offlineCount }}</strong></span>
                            </div>
                            <div class="flex items-center">
                                <span class="w-4 h-4 rounded-full bg-gray-300 mr-2"></span>
                                <span class="text-gray-600 dark:text-gray-300">Desconocido: <strong>{{ $unknownCount }}</strong></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Lista de Alertas (Solo si hay equipos caídos) -->
            @if($offlineDevices->count() > 0)
            <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-red-500">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4 text-red-600 dark:text-red-400 flex items-center">
                        ⚠️ Dispositivos que requieren atención ({{ $offlineDevices->count() }})
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Dispositivo</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Oficina</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Desde</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-white uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($offlineDevices as $device)
                                <tr>
                                    <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $device->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-300">{{ $device->ip_address }}</div>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white">{{ $device->office->name }}</td>
                                    <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white">{{ $device->last_checked_at ? $device->last_checked_at->diffForHumans() : '-' }}</td>
                                    <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                                        <a href="{{ route('devices.show', $device) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-semibold">Ver Historial</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    <!-- Cargar Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const ctx = document.getElementById('statusChart').getContext('2d');
        
        const statusChart = new Chart(ctx, {
            type: 'doughnut', // Gráfico tipo "Donut"
            data: {
                labels: ['Online', 'Offline', 'Desconocido'],
                datasets: [{
                    data: [{{ $onlineCount }}, {{ $offlineCount }}, {{ $unknownCount }}],
                    backgroundColor: [
                        'rgb(34, 197, 94)', // Green-500
                        'rgb(239, 68, 68)', // Red-500
                        'rgb(209, 213, 219)' // Gray-300
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // Ocultamos la leyenda por defecto para usar la nuestra
                    }
                },
                cutout: '70%', // Hace el agujero del centro más grande
            }
        });
    </script>
</x-app-layout>