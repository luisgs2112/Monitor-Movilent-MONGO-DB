<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('Detalles del Dispositivo') }}: {{ $device->name }}
            </h2>
            <a href="{{ route('devices.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Tarjeta de Información General -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Estado Actual</div>
                            <div class="text-xl font-bold flex items-center mt-1">
                                @if($device->status === 'online')
                                    <span class="text-green-600 dark:text-green-400">● Online</span>
                                @elseif($device->status === 'offline')
                                    <span class="text-red-600 dark:text-red-400">● Offline</span>
                                @else
                                    <span class="text-gray-500">● Desconocido</span>
                                @endif
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Dirección IP</div>
                            <div class="text-xl font-bold mt-1">{{ $device->ip_address }}</div>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Oficina</div>
                            <div class="text-xl font-bold mt-1">{{ $device->office->name }}</div>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Sistema Operativo</div>
                            <div class="text-lg font-bold mt-1 truncate" title="{{ $device->os_info }}">
                                {{ $device->os_icon }} {{ Str::limit($device->os_info, 20) ?: 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Rendimiento de CPU -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-white">Rendimiento de CPU (Últimas 24 Horas)</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="cpuChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Disponibilidad -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-white">Historial de Disponibilidad (7 Días)</h3>
                    <div class="relative h-48 w-full">
                        <canvas id="availabilityChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Configuración común para modo oscuro en gráficos
        Chart.defaults.color = document.documentElement.classList.contains('dark') ? '#9ca3af' : '#4b5563';
        Chart.defaults.borderColor = document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb';

        // 1. Gráfico de CPU
        const ctxCpu = document.getElementById('cpuChart').getContext('2d');
        new Chart(ctxCpu, {
            type: 'line',
            data: {
                labels: {!! json_encode($cpuLabels) !!},
                datasets: [{
                    label: 'Uso de CPU (%)',
                    data: {!! json_encode($cpuValues) !!},
                    borderColor: 'rgb(239, 68, 68)', // Rojo
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4, // Curvas suaves
                    pointRadius: 0 // Ocultar puntos para limpieza visual
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: { display: true, text: 'Porcentaje %' }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });

        // 2. Gráfico de Disponibilidad
        const ctxAvail = document.getElementById('availabilityChart').getContext('2d');
        new Chart(ctxAvail, {
            type: 'line',
            data: {
                labels: {!! json_encode($labels) !!},
                datasets: [{
                    label: 'Estado (1=Online, 0=Offline)',
                    data: {!! json_encode($statuses) !!},
                    borderColor: 'rgb(34, 197, 94)', // Verde
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2,
                    stepped: true, // Línea escalonada (cuadrada) ideal para estados binarios
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { min: 0, max: 1, ticks: { stepSize: 1 } } }
            }
        });
    </script>
</x-app-layout>