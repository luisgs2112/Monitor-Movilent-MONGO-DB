<!-- Contenedor de la Alerta (Oculto por defecto) -->
<div id="cpu-alert-popup" class="fixed top-5 right-5 z-50 hidden transition-transform duration-500 transform translate-x-full">
    <div class="bg-red-600 border-l-4 border-red-900 text-white p-4 rounded shadow-2xl flex items-center max-w-md">
        <div class="text-4xl mr-4 animate-pulse">🔥</div>
        <div>
            <h4 class="font-bold text-lg">¡ALERTA DE CPU!</h4>
            <p id="cpu-alert-message" class="text-sm font-medium opacity-95">El servidor está sobrecargado.</p>
            <p id="cpu-alert-time" class="text-xs mt-1 opacity-75"></p>
        </div>
        <button onclick="document.getElementById('cpu-alert-popup').classList.add('hidden')" class="ml-6 text-white hover:text-gray-300 font-bold text-xl">
            &times;
        </button>
    </div>
</div>

<!-- Elemento de Audio (Asegúrate de que el archivo exista en public/ding.mp3) -->
<audio id="alert-sound" src="{{ asset('ding.mp3') }}" preload="auto"></audio>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let seenNotifications = new Set(); // Para no repetir el sonido de la misma alerta

        // Revisar alertas cada 2 segundos (Más rápido)
        setInterval(checkCpuAlerts, 2000);

        function checkCpuAlerts() {
            // Llamamos a la ruta que crearemos en el paso 2
            fetch('/monitor/check-alerts')
                .then(response => response.json())
                .then(notifications => {
                    if (notifications.length > 0) {
                        notifications.forEach(note => {
                            // Verificamos si es una alerta de CPU y si es nueva para esta sesión
                            if (note.type === 'App\\Notifications\\DeviceHighCpuNotification' && !seenNotifications.has(note.id)) {
                                
                                // 1. Guardar ID para no repetir
                                seenNotifications.add(note.id);

                                // 2. Mostrar Popup
                                console.log('🔥 Alerta recibida:', note.data.message);
                                showPopup(note.data.message);

                                // 3. Tocar Sonido
                                playSound();
                            }
                        });
                    }
                })
                .catch(err => console.error('Error verificando alertas:', err));
        }

        function showPopup(message) {
            const popup = document.getElementById('cpu-alert-popup');
            const msgEl = document.getElementById('cpu-alert-message');
            const timeEl = document.getElementById('cpu-alert-time');

            msgEl.textContent = message;
            timeEl.textContent = new Date().toLocaleTimeString();
            
            popup.classList.remove('hidden', 'translate-x-full');
            popup.classList.add('translate-x-0');

            // Ocultar automáticamente después de 10 segundos
            setTimeout(() => {
                popup.classList.add('translate-x-full');
                setTimeout(() => popup.classList.add('hidden'), 500);
            }, 10000);
        }

        function playSound() {
            const audio = document.getElementById('alert-sound');
            // Intentar reproducir (los navegadores a veces bloquean audio sin interacción previa)
            audio.play().catch(e => console.log('Reproducción de audio bloqueada por el navegador:', e));
        }
    });
</script>
