<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
            {{ __('Editar Oficina') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('offices.update', $office) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Nombre de la Oficina')" />
                                <x-text-input id="name" class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 focus:ring-indigo-500 dark:focus:ring-indigo-600" type="text" name="name" :value="old('name', $office->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Branch Code -->
                            <div>
                                <x-input-label for="branch_code" :value="__('Código de Sucursal')" />
                                <x-text-input id="branch_code" class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 focus:ring-indigo-500 dark:focus:ring-indigo-600" type="text" name="branch_code" :value="old('branch_code', $office->branch_code)" />
                                <x-input-error :messages="$errors->get('branch_code')" class="mt-2" />
                            </div>

                            <!-- State -->
                            <div>
                                <x-input-label for="state" :value="__('Estado')" />
                                <x-text-input id="state" class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 focus:ring-indigo-500 dark:focus:ring-indigo-600" type="text" name="state" :value="old('state', $office->state)" placeholder="Ej: Distrito Capital" />
                                <x-input-error :messages="$errors->get('state')" class="mt-2" />
                            </div>

                            <!-- City -->
                            <div>
                                <x-input-label for="city" :value="__('Ciudad')" />
                                <x-text-input id="city" class="block mt-1 w-full dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 focus:ring-indigo-500 dark:focus:ring-indigo-600" type="text" name="city" :value="old('city', $office->city)" placeholder="Ej: Caracas" />
                                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="mt-4">
                            <x-input-label for="address" :value="__('Dirección Detallada')" />
                            <textarea id="address" name="address" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="3">{{ old('address', $office->address) }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Descripción / Notas')" />
                            <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="3">{{ old('description', $office->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('offices.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4">Cancelar</a>
                            <x-primary-button>
                                {{ __('Actualizar Oficina') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
