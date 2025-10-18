<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Chambres disponibles
        </x-slot>
        
        <x-slot name="description">
            Sélectionnez une chambre et remplissez les informations des participants
        </x-slot>
        
        {{ $this->table }}
    </x-filament::section>
</x-filament-widgets::widget>
