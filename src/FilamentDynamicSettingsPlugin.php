<?php

namespace Subham\FilamentDynamicSettings;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Subham\FilamentDynamicSettings\Pages\ManageSettings;
use Subham\FilamentDynamicSettings\Resources\SettingResource;

class FilamentDynamicSettingsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-dynamic-settings';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            ManageSettings::class,
        ])->resources([
            SettingResource::class
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
