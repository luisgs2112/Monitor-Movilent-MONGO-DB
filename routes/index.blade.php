<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notificaciones del Sistema') }}
            </h2>
            @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.read') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm text-blue-600 hover:text-blue-800 underline">
                    Marcar todas como leídas
                </button>
            </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($notifications->count() > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach($notifications as $notification)
                                <li class="py-4 {{ $notification->read_at ? 'opacity-50' : 'bg-red-50 -mx-6 px-6' }}">
                                    <div class="flex space-x-3">
                                        <div class="flex-1 space-y-1">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-sm font-medium text-red-600">
                                                    ⚠ Dispositivo Offline
                                                </h3>
                                                <p class="text-sm text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                            <p class="text-sm text-gray-900">
                                                {{ $notification->data['message'] }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                IP: {{ $notification->data['ip'] }}
                                            </p>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-4">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No tienes notificaciones.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>