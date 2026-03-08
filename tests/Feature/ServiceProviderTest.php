<?php

namespace Subham\FilamentDynamicSettings\Tests\Feature;

use Subham\FilamentDynamicSettings\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_config_is_published(): void
    {
        $config = config('filament-dynamic-settings');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('multi_tenant', $config);
        $this->assertArrayHasKey('modules', $config);
        $this->assertArrayHasKey('field_types', $config);
        $this->assertArrayHasKey('navigation', $config);
    }

    public function test_singleton_is_registered(): void
    {
        $instance = app('dynamic-settings');

        $this->assertInstanceOf(
            \Subham\FilamentDynamicSettings\Services\SettingsManager::class,
            $instance
        );
    }

    public function test_migrations_run_and_table_exists(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasTable('settings')
        );
    }

    public function test_settings_table_has_expected_columns(): void
    {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('settings');

        $expected = [
            'id', 'module', 'key', 'value', 'type',
            'options', 'label', 'description', 'order',
            'validation_rules', 'custom_validation_message',
            'is_active', 'created_at', 'updated_at',
        ];

        foreach ($expected as $column) {
            $this->assertContains($column, $columns, "Missing column: {$column}");
        }
    }

    public function test_default_config_values(): void
    {
        $this->assertFalse(config('filament-dynamic-settings.multi_tenant'));
        $this->assertNull(config('filament-dynamic-settings.tenant_model'));
        $this->assertSame('tenant_id', config('filament-dynamic-settings.tenant_column'));
        $this->assertSame('tabs', config('filament-dynamic-settings.layout'));
        $this->assertSame('general', config('filament-dynamic-settings.default_module'));
    }

    public function test_modules_are_configured(): void
    {
        $modules = config('filament-dynamic-settings.modules');

        $this->assertIsArray($modules);
        $this->assertArrayHasKey('general', $modules);
        $this->assertArrayHasKey('app', $modules);
        $this->assertArrayHasKey('email', $modules);
        $this->assertArrayHasKey('security', $modules);
    }

    public function test_blade_setting_directive_is_registered(): void
    {
        $directives = app('blade.compiler')->getCustomDirectives();

        $this->assertArrayHasKey('setting', $directives);
        $this->assertArrayHasKey('rawsetting', $directives);
    }

    public function test_translations_are_loaded(): void
    {
        $translated = __('filament-dynamic-settings::settings.navigation.label');

        $this->assertSame('Settings', $translated);
    }

    public function test_notification_translation_is_correct(): void
    {
        $translated = __('filament-dynamic-settings::settings.notifications.saved');

        $this->assertSame('Settings saved successfully.', $translated);
    }
}
