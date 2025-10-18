<x-filament-panels::page>
    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Bienvenue sur le système de réservation de chambres
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Gérez vos chambres et réservez votre place pour le voyage
            </p>
        </div>
        
        @if(session('admin'))
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-blue-800 dark:text-blue-200 font-medium">
                        Mode administrateur activé
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
