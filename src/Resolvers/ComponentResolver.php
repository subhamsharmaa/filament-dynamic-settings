<?php

namespace Subham\FilamentDynamicSettings\Resolvers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Subham\FilamentDynamicSettings\Models\Setting;

class ComponentResolver
{
    protected static array $customResolvers = [];

    public static function registerResolver(string $type, callable $resolver): void
    {
        static::$customResolvers[$type] = $resolver;
    }

    public static function resolve(Setting $setting): Component
    {
        // 1. Check registered callable resolvers first
        if (isset(static::$customResolvers[$setting->type])) {
            return call_user_func(static::$customResolvers[$setting->type], $setting);
        }

        // 2. Check config-based custom components
        $customComponents = config('filament-dynamic-settings.custom_components', []);
        if (isset($customComponents[$setting->type])) {
            $componentClass = $customComponents[$setting->type]['component'];
            if (class_exists($componentClass)) {
                return $componentClass::make($setting->key)
                    ->label($setting->label ?: str($setting->key)->title()->toString())
                    ->helperText($setting->description)
                    ->default($setting->getRawValue())
                    ->translateLabel();
            }
        }

        // 3. Resolve built-in component
        return static::resolveDefaultComponent($setting);
    }

    protected static function resolveDefaultComponent(Setting $setting): Component
    {
        $component = match ($setting->type) {
            'text' => TextInput::make($setting->key),
            'textarea' => Textarea::make($setting->key)->rows(3),
            'number' => TextInput::make($setting->key)->numeric(),
            'boolean' => Toggle::make($setting->key),
            'select' => Select::make($setting->key)
                ->options($setting->options ?: []),
            'json' => KeyValue::make($setting->key),
            'url' => TextInput::make($setting->key)->url(),
            'email' => TextInput::make($setting->key)->email(),
            'password' => TextInput::make($setting->key)->password(),
            'date' => DatePicker::make($setting->key),
            'file' => FileUpload::make($setting->key),
            'rich_text' => RichEditor::make($setting->key),
            'date_time' => DateTimePicker::make($setting->key),
            default => TextInput::make($setting->key),
        };

        $component = $component
            ->label($setting->label ?: str($setting->key)->title()->toString())
            ->helperText($setting->description)
            ->default($setting->getRawValue())
            ->required($setting->options['required'] ?? false)
            ->translateLabel();

        if (! empty($setting->validation_rules) && is_array($setting->validation_rules)) {
            $rules = static::buildValidationRules($setting->validation_rules);
            if (! empty($rules)) {
                $component = $component->rules($rules);
            }
        }

        if ($setting->custom_validation_message) {
            $component = $component->validationMessages(
                is_array($setting->custom_validation_message)
                    ? $setting->custom_validation_message
                    : ['default' => $setting->custom_validation_message]
            );
        }

        return $component;
    }

    /**
     * Build Laravel validation rules from the key-value config.
     * Trusts admin input — validation of rules should happen at write time, not render time.
     */
    protected static function buildValidationRules(array $validationRules): array
    {
        return collect($validationRules)
            ->map(fn ($ruleValue, $ruleName) => empty($ruleValue) ? $ruleName : "{$ruleName}:{$ruleValue}")
            ->values()
            ->all();
    }

    public static function getAvailableTypes(): array
    {
        $defaultTypes = config('filament-dynamic-settings.field_types', []);
        $customTypes = array_keys(config('filament-dynamic-settings.custom_components', []));
        $registeredTypes = array_keys(static::$customResolvers);

        $extra = array_merge($customTypes, $registeredTypes);

        return array_merge($defaultTypes, array_combine($extra, $extra));
    }
}
