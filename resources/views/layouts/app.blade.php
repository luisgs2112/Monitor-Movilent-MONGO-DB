<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https:
        <link href="https:

        
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

           
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- Componente de Alerta de CPU en Tiempo Real -->
        <x-cpu-alert />

        <!-- Notificación Emergente (Toast) Global -->
        <div id="toast-notification" class="fixed bottom-5 right-5 bg-white dark:bg-gray-800 border-l-4 border-red-600 text-gray-900 dark:text-gray-100 px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-500 translate-y-32 opacity-0 flex items-center gap-4 z-50 max-w-md border border-gray-200 dark:border-gray-700">
            <div class="text-red-500 text-2xl">🔔</div>
            <div>
                <h4 class="font-bold text-gray-800 dark:text-gray-200">¡Nueva Alerta!</h4>
                <p id="toast-message" class="text-sm text-gray-600 dark:text-gray-400 mt-1">Notificación recibida.</p>
            </div>
            <button onclick="document.getElementById('toast-notification').classList.add('translate-y-32', 'opacity-0')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 ml-auto">✕</button>
        </div>

        <script>
            
            const alertSound = new Audio("{{ asset('alert.mp3') }}");
            let isAudioEnabled = localStorage.getItem('audio_enabled') === 'true';

            
            const btnAudio = document.getElementById('btn-enable-audio');

            function updateButtonUI() {
                if (!btnAudio) return;
                if (isAudioEnabled) {
                    btnAudio.innerHTML = '🔊 Sonido Activado';
                    btnAudio.className = 'ml-3 flex items-center text-xs bg-green-100 dark:bg-green-900 hover:bg-green-200 dark:hover:bg-green-800 text-green-800 dark:text-green-100 px-3 py-1 rounded-full transition-colors border border-green-200 dark:border-green-700 cursor-pointer';
                } else {
                    btnAudio.innerHTML = '🔇 Activar Sonido';
                    btnAudio.className = 'ml-3 flex items-center text-xs bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200 px-3 py-1 rounded-full transition-colors cursor-pointer';
                }
            }

            if (btnAudio) {
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
                        }).catch(e => console.error("Error activando audio:", e));
                    }
                });
            }

            
            setInterval(function() {
                
                if (window.location.search.includes('search')) return;

                const url = new URL(window.location.href);
                url.searchParams.set('_t', new Date().getTime());

                fetch(url.toString(), { 
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                    credentials: 'include',
                    cache: 'no-store'
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    
                    const newTbody = doc.querySelector('#devices-table tbody');
                    const currentTbody = document.querySelector('#devices-table tbody');
                    if (newTbody && currentTbody && newTbody.innerHTML !== currentTbody.innerHTML) {
                        currentTbody.innerHTML = newTbody.innerHTML;
                        const tableContainer = document.getElementById('devices-table');
                        if(tableContainer) {
                            tableContainer.style.transition = 'opacity 0.2s';
                            tableContainer.style.opacity = '0.5';
                            setTimeout(() => tableContainer.style.opacity = '1', 200);
                        }
                    }

                    
                    const liveStatus = document.getElementById('live-status');
                    if (liveStatus) liveStatus.innerText = 'Actualizado: ' + new Date().toLocaleTimeString();

                    
                    const newBell = doc.querySelector('#notification-bell');
                    const currentBell = document.querySelector('#notification-bell');

                    if (newBell && currentBell) {
                        const newCount = parseInt(newBell.getAttribute('data-count') || '0');
                        const currentCount = parseInt(currentBell.getAttribute('data-count') || '0');

                        if (newCount > currentCount) {
                            if (isAudioEnabled) {
                                let repetitions = 3;
                                const playAlarmSequence = () => {
                                    if (repetitions <= 0) return;
                                    alertSound.currentTime = 0;
                                    alertSound.play().catch(e => console.error(e));
                                    repetitions--;
                                    if (repetitions > 0) setTimeout(playAlarmSequence, 10000);
                                };
                                playAlarmSequence();
                            }

                            const message = newBell.getAttribute('data-latest-message');
                            const toast = document.getElementById('toast-notification');
                            const toastMsg = document.getElementById('toast-message');
                            
                            if (toast && message) {
                                toastMsg.textContent = message;
                                toast.classList.remove('translate-y-32', 'opacity-0');
                                setTimeout(() => toast.classList.add('translate-y-32', 'opacity-0'), 8000);
                            }
                        }
                        
                        currentBell.innerHTML = newBell.innerHTML;
                        currentBell.setAttribute('data-count', newCount);
                        currentBell.setAttribute('data-latest-message', newBell.getAttribute('data-latest-message'));
                    }
                })
                .catch(error => console.error('Error polling:', error));
            }, 5000);
        </script>
    </body>
</html>
