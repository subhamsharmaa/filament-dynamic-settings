<?php

namespace Subham\FilamentDynamicSettings\Resolvers;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
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
        if (isset(static::$customResolvers[$setting->type])) {
            return call_user_func(static::$customResolvers[$setting->type], $setting);
        }

        $customComponents = config('filament-dynamic-settings.custom_components', []);
        if (isset($customComponents[$setting->type])) {
            $componentClass = $customComponents[$setting->type]['component'];
            if (class_exists($componentClass)) {
                return $componentClass::make($setting->key)
                    ->label($setting->label ?: str($setting->key)->title())
                    ->helperText($setting->description)
                    ->default($setting->value)
                    ->translateLabel();
            }
        }

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
            ->default($setting->value)
            ->required($setting->options['required'] ?? false)
            ->translateLabel();

        if ($setting->validation_rules && is_array($setting->validation_rules)) {
            $rules = self::buildValidationRules($setting->validation_rules);
            if (!empty($rules)) {
                $component = $component->rules($rules);
            }
        }

        return $component;
    }

    protected static function buildValidationRules(array $validationRules): array
    {
        $rules = [];

        foreach ($validationRules as $ruleName => $ruleValue) {
            try {
                $rule = empty($ruleValue) ? $ruleName : $ruleName . ':' . $ruleValue;

                if (self::isValidValidationRule($rule)) {
                    $rules[] = $rule;
                } else {
                    // invalid rule
                }
            } catch (\Exception $e) {
                // invalide rule
            }
        }

        return $rules;
    }

    /**
     * Test if a validation rule is valid by attempting to use it
     */
    protected static function isValidValidationRule(string $rule): bool
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make(
                ['test_field' => 'test_value'],
                ['test_field' => $rule]
            );

            $validator->passes();
            
            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }


    public static function getAvailableTypes(): array
    {
        $defaultTypes = config('filament-dynamic-settings.field_types', []);
        $customTypes = array_keys(config('filament-dynamic-settings.custom_components', []));
        $registeredTypes = array_keys(static::$customResolvers);

        return array_merge($defaultTypes, array_combine($customTypes, $customTypes), array_combine($registeredTypes, $registeredTypes));
    }
}
