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
                                                {{ $device->os_icon }}
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
                                        @if($device->uptime)
                                            <!-- Limpiamos el formato (12345) 10:00:00 para mostrar solo el tiempo -->
                                            <span class="text-gray-700 dark:text-white font-mono text-xs">
                                                {{ Str::after($device->uptime, ') ') }}
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

    <!-- Notificación Emergente (Toast) estilo YouTube -->
    <div id="toast-notification" class="fixed bottom-5 right-5 bg-white dark:bg-gray-800 border-l-4 border-red-600 text-gray-900 dark:text-gray-100 px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-500 translate-y-32 opacity-0 flex items-center gap-4 z-50 max-w-md border border-gray-200 dark:border-gray-700">
        <div class="text-red-500 text-2xl">
            🔔
        </div>
        <div>
            <h4 class="font-bold text-gray-800 dark:text-gray-200">¡Dispositivo Desconectado!</h4>
            <p id="toast-message" class="text-sm text-gray-600 dark:text-gray-400 mt-1">Dispositivo desconectado.</p>
        </div>
        <button onclick="document.getElementById('toast-notification').classList.add('translate-y-32', 'opacity-0')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 ml-auto">
            ✕
        </button>
    </div>

    <script>
        // Configuración de Audio Global
        const alertSound = new Audio("{{ asset('alert.mp3') }}");
        
        // Recuperar preferencia guardada (Persistencia)
        let isAudioEnabled = localStorage.getItem('audio_enabled') === 'true';

        // Botón para "desbloquear" el audio en el navegador
        const btnAudio = document.getElementById('btn-enable-audio');

        // Función para actualizar visualmente el botón
        function updateButtonUI() {
            if (isAudioEnabled) {
                btnAudio.innerHTML = '🔊 Sonido Activado';
                btnAudio.className = 'ml-3 flex items-center text-xs bg-green-100 dark:bg-green-900 hover:bg-green-200 dark:hover:bg-green-800 text-green-800 dark:text-green-100 px-3 py-1 rounded-full transition-colors border border-green-200 dark:border-green-700 cursor-pointer';
            } else {
                btnAudio.innerHTML = '🔇 Activar Sonido';
                btnAudio.className = 'ml-3 flex items-center text-xs bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 px-3 py-1 rounded-full transition-colors cursor-pointer';
            }
        }

        // Inicializar estado del botón al cargar la página
        updateButtonUI();

        btnAudio.addEventListener('click', function() {
            if (isAudioEnabled) {
                isAudioEnabled = false;
                localStorage.setItem('audio_enabled', 'false');
                updateButtonUI();
            } else {
                alertSound.play().then(() => {
                    alertSound.pause();
                    alertSound.currentTime = 0;
                    isAudioEnabled = true;
                    localStorage.setItem('audio_enabled', 'true');
                    updateButtonUI();
                }).catch(e => {
                    console.error("Error activando audio:", e);
                    alert("No se pudo activar el audio.\n\nPosible causa: El archivo 'alert.mp3' no se encuentra en la carpeta 'public' o el formato no es compatible.");
                });
            }
        });

        // Actualización automática "silenciosa" cada 5 segundos
        setInterval(function() {
            console.log('Iniciando chequeo de actualización...');
            
            // Si hay una búsqueda activa, no actualizamos para no interrumpir lo que escribes
            if (window.location.search.includes('search')) return;

            // Agregamos un timestamp para evitar que el navegador use caché y traiga datos viejos
            const url = new URL(window.location.href);
            url.searchParams.set('_t', new Date().getTime());

            fetch(url.toString(), { 
                headers: { "X-Requested-With": "XMLHttpRequest" },
                credentials: 'include',
                cache: 'no-store'
            })
                .then(response => response.text())
                .then(html => {
                    // Convertimos el texto recibido en un documento HTML virtual
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // 1. Actualizar el cuerpo de la tabla (tbody)
                    // Usamos tbody para ser más específicos y evitar reemplazar encabezados
                    const newTbody = doc.querySelector('#devices-table tbody');
                    const currentTbody = document.querySelector('#devices-table tbody');
                    
                    if (newTbody && currentTbody) {
                        if (newTbody.innerHTML !== currentTbody.innerHTML) {
                            console.log('Cambios detectados en los dispositivos. Actualizando vista...');
                            currentTbody.innerHTML = newTbody.innerHTML;
                            
                            // Efecto visual de actualización
                            const tableContainer = document.getElementById('devices-table');
                            tableContainer.style.transition = 'opacity 0.2s';
                            tableContainer.style.opacity = '0.5';
                            setTimeout(() => tableContainer.style.opacity = '1', 200);
                        }
                    } else if (!newTbody) {
                        console.error('Error: No se encontró la tabla en la respuesta. Es posible que la sesión haya expirado o se haya redirigido al login.');
                    }

                    // 2. Actualizar Indicador de Tiempo
                    const now = new Date();
                    document.getElementById('live-status').innerText = 'Actualizado: ' + now.toLocaleTimeString();

                    // --- Lógica de Notificación Sonora ---
                    const newBell = doc.querySelector('#notification-bell');
                    const currentBell = document.querySelector('#notification-bell');

                    if (newBell && currentBell) {
                        const newCount = parseInt(newBell.getAttribute('data-count') || '0');
                        const currentCount = parseInt(currentBell.getAttribute('data-count') || '0');

                        // Si hay más notificaciones que antes, sonar alarma y mostrar mensaje
                        if (newCount > currentCount) {
                            console.log('¡ALERTA! Nueva notificación detectada (' + currentCount + ' -> ' + newCount + ').');
                            
                            // Reproducir sonido solo si el usuario lo habilitó
                            if (isAudioEnabled) {
                                alertSound.play()
                                    .then(() => console.log('Sonido reproducido correctamente.'))
                                    .catch(e => console.error('Error al reproducir sonido:', e));
                            }

                            // Mostrar Toast Visual
                            const message = newBell.getAttribute('data-latest-message');
                            const toast = document.getElementById('toast-notification');
                            const toastMsg = document.getElementById('toast-message');
                            
                            if (toast && message) {
                                toastMsg.textContent = message;
                                toast.classList.remove('translate-y-32', 'opacity-0'); // Mostrar (deslizar hacia arriba)
                                
                                // Ocultar automáticamente después de 8 segundos
                                setTimeout(() => {
                                    toast.classList.add('translate-y-32', 'opacity-0');
                                }, 8000);
                            }
                        }
                        
                        // Actualizar el icono de la campana (para que aparezca el punto rojo sin recargar)
                        currentBell.innerHTML = newBell.innerHTML;
                        currentBell.setAttribute('data-count', newCount);
                        currentBell.setAttribute('data-latest-message', newBell.getAttribute('data-latest-message'));
                    }
                })
                .catch(error => console.error('Error actualizando tabla:', error));
        }, 5000); // Consulta cada 5 segundos
    </script>
</x-app-layout>