<?php

namespace Subham\FilamentDynamicSettings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'validation_rules'
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
        'validation_rules' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        
        if (config('filament-dynamic-settings.multi_tenant', false)) {
            static::creating(function ($model) {
                $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');
                
                if (!$model->{$tenantColumn} && static::getCurrentTenantId()) {
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
            $this->fillable[] = $tenantColumn;
        }
    }

    protected function value(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return match ($this->type) {
                    'boolean' => (bool) $value,
                    'number' => is_numeric($value) ? (int) $value : $value,
                    'json' => json_decode($value, true),
                    default => $value,
                };
            },
            set: function ($value) {
                return match ($this->type) {
                    'boolean' => $value ? '1' : '0',
                    'json' => json_encode($value),
                    default => (string) $value,
                };
            }
        );
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
        if (!config('filament-dynamic-settings.multi_tenant', false)) {
            return $query;
        }

        $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');
        $tenantId = $tenantId ?? static::getCurrentTenantId();
        
        return $query->where($tenantColumn, $tenantId);
    }

    public static function get(string $key, string $module = 'general', $default = null, $tenantId = null)
    {
        $query = static::where('module', $module)
            ->where('key', $key)
            ->where('is_active', true);

        if (config('filament-dynamic-settings.multi_tenant', false)) {
            $query->forTenant($tenantId);
        }

        $setting = $query->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value, string $module = 'general', array $options = [], $tenantId = null)
    {
        $data = [
            'module' => $module,
            'key' => $key,
            'value' => $value,
        ];

        if (config('filament-dynamic-settings.multi_tenant', false)) {
            $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');
            $data[$tenantColumn] = $tenantId ?? static::getCurrentTenantId();
        }

        $whereClause = ['module' => $module, 'key' => $key];
        
        if (config('filament-dynamic-settings.multi_tenant', false)) {
            $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');
            $whereClause[$tenantColumn] = $data[$tenantColumn];
        }

        return static::updateOrCreate($whereClause, array_merge($data, $options));
    }

    public function tenant(): BelongsTo
    {
        if (!config('filament-dynamic-settings.multi_tenant', false)) {
            throw new \Exception('Tenant relationship is only available in multi-tenant mode');
        }

        $tenantModel = config('filament-dynamic-settings.tenant_model');
        $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');
        
        if (!$tenantModel) {
            throw new \Exception('Tenant model must be configured for multi-tenant mode');
        }

        return $this->belongsTo($tenantModel, $tenantColumn);
    }

    protected static function getCurrentTenantId()
    {
        if (class_exists('\Filament\Facades\Filament')) {
            $tenant = \Filament\Facades\Filament::getTenant();
            if ($tenant) {
                return $tenant->getKey();
            }
        }
        
        if (session()->has('tenant_id')) {
            return session('tenant_id');
        }

        return null;
    }
}