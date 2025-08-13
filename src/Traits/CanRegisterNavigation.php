<?php

namespace Subham\FilamentDynamicSettings\Traits;

use Filament\Facades\Filament;

trait CanRegisterNavigation
{

    protected static function shouldRegisterComponent(string $component): bool
    {
        $config = config("filament-dynamic-settings.$component");

        if (empty($config['register'])) {
            return false;
        }

        $currentPanelId = Filament::getCurrentPanel()->getId();

        if (!empty($config['exclude_on_panels'])) {
            return !in_array($currentPanelId, $config['exclude_on_panels']);
        }

        return true;
    }

    public static function shouldRegisterResource(): bool
    {
        return static::shouldRegisterComponent('resource');
    }

    public static function shouldRegisterPage(): bool
    {
        return static::shouldRegisterComponent('page');
    }
}
