<x-filament-widgets::widget class="flex gap-8">
    <x-filament::section>
        @php
            $user = $this->getUserData();
        @endphp

        <div class="flex items-center gap-4">

            <div class="w-12 h-12 p-2 rounded-full bg-primary-500 text-white 
                        flex items-center justify-center text-xl font-bold shadow">
                {{ strtoupper(substr($user['name'], 0, 1)) }}
            </div>

            <div class="flex flex-col leading-tight">
                <span class="font-semibold text-base">{{ $user['name'] }}</span>
                <span class="text-gray-500 text-sm">{{ $user['email'] }}</span>
            </div>

            <form class="ml-auto" method="GET" action="{{ route('backoffice.logout') }}">
                @csrf
                <button class="px-3 py-1.5 text-sm bg-danger-600 hover:bg-danger-700 
                               text-white rounded-lg shadow transition">
                    Déconnexion
                </button>
            </form>
        </div>
    </x-filament::section>

    @if($this->isAdmin())
    <x-filament::section class="flex items-center justify-center">
            <div class="flex items-center justify-center gap-4 flex-nowrap">

                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"
                     class="w-6 h-6" style="color:#FBBF24;">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 
                       3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 
                       12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>

                <span class="font-semibold whitespace-nowrap gap-4">
                    Mode Admin
                    <span class="px-2 py-0.5 text-xs rounded-full" style="background-color:#FEF3C7; color:#B45309;">
                        Activé
                    </span>
                </span>

            </div>
        </x-filament::section>
    @endif
</x-filament-widgets::widget>
