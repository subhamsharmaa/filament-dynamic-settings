<?php

namespace Subham\FilamentDynamicSettings\Facades;

use Illuminate\Support\Facades\Facade;
/**
 * @method static mixed get(string $key, string $module = 'general', $default = null, $tenantId = null)
 * @method static mixed raw(string $key, string $module = 'general', $default = null, $tenantId = null)
 * @method static bool set(string $key, $value, string $module = 'general', $tenantId = null)
 * @method static array module(string $module, $tenantId = null)
 * @see \Subham\FilamentDynamicSettings\Services\SettingsManager
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dynamic-settings';
    }
}