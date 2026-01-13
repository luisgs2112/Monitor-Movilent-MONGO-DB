<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Registrar Nuevo Dispositivo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('devices.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Office Selection -->
                            <div class="col-span-1 md:col-span-2">
                                <x-input-label for="office_id" :value="__('Oficina Asignada')" />
                                <select id="office_id" name="office_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">Seleccione una oficina...</option>
                                    @foreach($offices as $office)
                                        <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>
                                            {{ $office->name }} ({{ $office->city }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('office_id')" class="mt-2" />
                            </div>

                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Nombre del Equipo')" />
                                <x-text-input id="name" class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 focus:ring-indigo-500 dark:focus:ring-indigo-600" type="text" name="name" :value="old('name')" placeholder="Ej: Router Principal Piso 1" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- IP Address -->
                            <div>
                                <x-input-label for="ip_address" :value="__('Dirección IP')" />
                                <x-text-input id="ip_address" class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 focus:ring-indigo-500 dark:focus:ring-indigo-600" type="text" name="ip_address" :value="old('ip_address')" placeholder="192.168.1.1" required />
                                <x-input-error :messages="$errors->get('ip_address')" class="mt-2" />
                            </div>

                            <!-- Type -->
                            <div>
                                <x-input-label for="type" :value="__('Tipo de Dispositivo')" />
                                <select id="type" name="type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="router">Router</option>
                                    <option value="switch">Switch</option>
                                    <option value="server">Servidor</option>
                                    <option value="other">Otro</option>
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            <!-- SNMP Port -->
                            <div>
                                <x-input-label for="snmp_port" :value="__('Puerto SNMP')" />
                                <x-text-input id="snmp_port" class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 focus:ring-indigo-500 dark:focus:ring-indigo-600" type="number" name="snmp_port" :value="old('snmp_port', 161)" required />
                                <x-input-error :messages="$errors->get('snmp_port')" class="mt-2" />
                            </div>

                            <!-- SNMP Community -->
                            <div>
                                <x-input-label for="snmp_community" :value="__('Comunidad SNMP')" />
                                <x-text-input id="snmp_community" class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 focus:ring-indigo-500 dark:focus:ring-indigo-600" type="text" name="snmp_community" :value="old('snmp_community', 'public')" required />
                                <x-input-error :messages="$errors->get('snmp_community')" class="mt-2" />
                            </div>

                            <!-- Active Status -->
                            <div class="flex items-center mt-4">
                                <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" checked>
                                <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">{{ __('Activar Monitoreo') }}</label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('devices.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4">Cancelar</a>
                            <x-primary-button>{{ __('Guardar Dispositivo') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
