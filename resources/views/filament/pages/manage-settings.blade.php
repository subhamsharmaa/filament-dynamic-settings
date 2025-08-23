<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        
        <x-filament::button type="submit" class="mt-4">
        {{ __('filament-dynamic-settings::settings.actions.submit') }}
    </x-filament::button>
    </form>
</x-filament-panels::page>