<?php

namespace Subham\FilamentDynamicSettings\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

trait HasSettingsValue
{
    /**
     * Get the properly formatted value based on the setting type
     */
    public function getFormattedValue()
    {
        if (is_null($this->value) || $this->value === '') {
            return null;
        }

        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'number', 'integer' => is_numeric($this->value) ? (int) $this->value : $this->value,
            'numeric' => is_numeric($this->value) ? (float) $this->value : $this->value,
            'json' => $this->parseJson($this->value),
            'file', 'image' => $this->getFileUrl($this->value),
            'date' => $this->parseDate($this->value),
            'date_time' => $this->parseDate($this->value),
            default => $this->value,
        };
    }

    /**
     * Parse JSON value
     */
    protected function parseJson($value)
    {
        try {
            return json_decode($value, true) ?? $value;
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Get file URL
     */
    protected function getFileUrl($value)
    {
        if (empty($value)) return null;
        
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (Storage::exists($value)) {
            return Storage::url($value);
        }

        return asset($value);
    }

    /**
     * Parse date value
     */
    protected function parseDate($value)
    {
        if (empty($value)) return null;
        
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Get raw value (bypass formatting)
     */
    public function getRawValue()
    {
        return $this->value;
    }
}