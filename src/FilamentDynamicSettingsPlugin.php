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

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        if (config('filament-dynamic-settings.page.register', true)) {
            $panel->pages([
                ManageSettings::class,
            ]);
        }

        if (config('filament-dynamic-settings.resource.register', true)) {
            $panel->resources([
                SettingResource::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
