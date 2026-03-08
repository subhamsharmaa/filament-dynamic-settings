<?php

namespace Subham\FilamentDynamicSettings\Models;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Subham\FilamentDynamicSettings\Traits\HasSettingsValue;

class Setting extends Model
{
    use HasSettingsValue;

    protected $fillable = [
        'module',
        'key',
        'value',
        'type',
        'options',
        'label',
        'description',
        'order',
        'is_active',
        'validation_rules',
        'custom_validation_message',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
        'validation_rules' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        if (config('filament-dynamic-settings.multi_tenant', false)) {
            static::creating(function ($model) {
                $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');

                if (! $model->{$tenantColumn} && static::getCurrentTenantId()) {
                    $model->{$tenantColumn} = static::getCurrentTenantId();
                }
            });
        }
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (config('filament-dynamic-settings.multi_tenant', false)) {
            $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');
            if (! in_array($tenantColumn, $this->fillable)) {
                $this->fillable[] = $tenantColumn;
            }
        }
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTenant($query, $tenantId = null)
    {
        if (! config('filament-dynamic-settings.multi_tenant', false)) {
            return $query;
        }

        $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');
        $tenantId = $tenantId ?? static::getCurrentTenantId();

        return $query->where($tenantColumn, $tenantId);
    }

    public static function set(string $key, $value, string $module = 'general', array $options = [], $tenantId = null): void
    {
        $isMultiTenant = config('filament-dynamic-settings.multi_tenant', false);
        $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');

        $data = [
            'module' => $module,
            'key' => $key,
            'value' => $value,
        ];

        $whereClause = ['module' => $module, 'key' => $key];

        if ($isMultiTenant) {
            $resolvedTenantId = $tenantId ?? static::getCurrentTenantId();
            $data[$tenantColumn] = $resolvedTenantId;
            $whereClause[$tenantColumn] = $resolvedTenantId;
        }

        static::updateOrCreate($whereClause, array_merge($data, $options));
        static::forget($key, $module, $tenantId);
    }

    public function tenant(): BelongsTo
    {
        if (! config('filament-dynamic-settings.multi_tenant', false)) {
            throw new RuntimeException('Tenant relationship is only available in multi-tenant mode.');
        }

        $tenantModel = config('filament-dynamic-settings.tenant_model');
        $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');

        if (! $tenantModel) {
            throw new RuntimeException('Tenant model must be configured for multi-tenant mode.');
        }

        return $this->belongsTo($tenantModel, $tenantColumn);
    }

    public static function getCurrentTenantId(): mixed
    {
        try {
            if (class_exists(Filament::class)) {
                $tenant = Filament::getTenant();
                if ($tenant) {
                    return $tenant->getKey();
                }
            }
        } catch (\Throwable) {
        }

        if (session()->has('tenant_id')) {
            return session('tenant_id');
        }

        return null;
    }

    public static function forget(string $key, string $module = 'general', $tenantId = null): void
    {
        $resolvedTenantId = $tenantId ?? static::getCurrentTenantId();
        $cacheKey = "settings:{$resolvedTenantId}:{$module}:{$key}";
        Cache::forget($cacheKey);
    }
}