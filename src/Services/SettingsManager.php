<?php
namespace Subham\FilamentDynamicSettings\Services;

use Subham\FilamentDynamicSettings\Models\Setting;

class SettingsManager
{
     /**
     * Get setting value with proper formatting
     */
    public function get(string $key, string $module = 'general', $default = null, $tenantId = null)
    {
        $query = Setting::where('module', $module)
            ->where('key', $key)
            ->where('is_active', true);

        if (config('filament-dynamic-settings.multi_tenant', false)) {
            $query->forTenant($tenantId);
        }

        $setting = $query->first();

        return $setting ? $setting->getFormattedValue() : $default;
    }

    /**
     * Get raw setting value
     */
    public function raw(string $key, string $module = 'general', $default = null, $tenantId = null)
    {
        $query = Setting::where('module', $module)
            ->where('key', $key)
            ->where('is_active', true);

        if (config('filament-dynamic-settings.multi_tenant', false)) {
            $query->forTenant($tenantId);
        }

        $setting = $query->first();

        return $setting ? $setting->getRawValue() : $default;
    }

    /**
     * Set setting value
     */
    public function set(string $key, $value, string $module = 'general', $tenantId = null)
    {
        $query = Setting::where('module', $module)->where('key', $key);

        if (config('filament-dynamic-settings.multi_tenant', false)) {
            $query->forTenant($tenantId);
        }

        $setting = $query->first();

        if ($setting) {
            $setting->update(['value' => $value]);
            return true;
        }

        return false;
    }

    /**
     * Get all settings for a module
     */
    public function module(string $module, $tenantId = null)
    {
        $query = Setting::where('module', $module)
            ->where('is_active', true)
            ->orderBy('order');

        if (config('filament-dynamic-settings.multi_tenant', false)) {
            $query->forTenant($tenantId);
        }

        return $query->get()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->getFormattedValue()];
        })->toArray();
    }
}
