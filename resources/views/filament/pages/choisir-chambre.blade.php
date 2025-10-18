<x-filament-panels::page>
    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Choisir ma chambre
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Sélectionnez une chambre et remplissez les informations des participants
            </p>
        </div>
        
        <form wire:submit="save">
            {{ $this->form }}
            
            <div class="flex justify-end mt-6">
                <x-filament::button type="submit" size="lg">
                    Réserver la chambre
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
