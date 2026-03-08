<?php

namespace Subham\FilamentDynamicSettings\Services;

use Illuminate\Support\Facades\Cache;
use Subham\FilamentDynamicSettings\Models\Setting;

class SettingsManager
{
    /**
     * Build a normalized cache key for a setting.
     */
    protected function cacheKey(string $key, string $module, $tenantId = null): string
    {
        $resolvedTenantId = $tenantId ?? Setting::getCurrentTenantId();

        return "settings:{$resolvedTenantId}:{$module}:{$key}";
    }

    /**
     * Get setting value with proper formatting.
     */
    public function get(string $key, string $module = 'general', $default = null, $tenantId = null): mixed
    {
        $cacheKey = $this->cacheKey($key, $module, $tenantId);

        return Cache::rememberForever($cacheKey, function () use ($key, $module, $default, $tenantId) {
            $query = Setting::where('module', $module)
                ->where('key', $key)
                ->where('is_active', true);

            if (config('filament-dynamic-settings.multi_tenant', false)) {
                $query->forTenant($tenantId);
            }

            $setting = $query->first();

            return $setting ? $setting->getFormattedValue() : $default;
        });
    }

    /**
     * Get raw setting value (bypasses formatting and cache).
     */
    public function raw(string $key, string $module = 'general', $default = null, $tenantId = null): mixed
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
     * Set setting value and invalidate cache.
     */
    public function set(string $key, $value, string $module = 'general', $tenantId = null): bool
    {
        $query = Setting::where('module', $module)->where('key', $key);

        if (config('filament-dynamic-settings.multi_tenant', false)) {
            $query->forTenant($tenantId);
        }

        $setting = $query->first();

        if ($setting) {
            $setting->update(['value' => $value]);
            Cache::forget($this->cacheKey($key, $module, $tenantId));

            return true;
        }

        return false;
    }

    /**
     * Get all settings for a module.
     */
    public function module(string $module, $tenantId = null): array
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
