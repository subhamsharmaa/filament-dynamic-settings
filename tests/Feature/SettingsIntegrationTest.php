<?php

namespace Subham\FilamentDynamicSettings\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Subham\FilamentDynamicSettings\Facades\Settings;
use Subham\FilamentDynamicSettings\Models\Setting;
use Subham\FilamentDynamicSettings\Tests\TestCase;

class SettingsIntegrationTest extends TestCase
{
    // -------------------------------------------------------
    //  End-to-end: create → get → update → get (via facade)
    // -------------------------------------------------------

    public function test_full_lifecycle_via_facade(): void
    {
        // 1. Create a setting via model
        $this->createSetting([
            'key' => 'site_name',
            'value' => 'My Site',
            'module' => 'general',
            'type' => 'text',
        ]);

        // 2. Read via facade (should be cached)
        $this->assertSame('My Site', Settings::get('site_name'));

        // 3. Update via facade
        Settings::set('site_name', 'Updated Site');

        // 4. Read again (cache should be invalidated, return new value)
        $this->assertSame('Updated Site', Settings::get('site_name'));
    }

    public function test_full_lifecycle_with_typed_values(): void
    {
        $this->createSetting([
            'key' => 'max_users',
            'value' => '100',
            'module' => 'app',
            'type' => 'number',
        ]);

        // Formatted value should be int
        $this->assertSame(100, Settings::get('max_users', 'app'));

        // Raw value should be string
        $this->assertSame('100', Settings::raw('max_users', 'app'));
    }

    public function test_full_lifecycle_with_boolean(): void
    {
        $this->createSetting([
            'key' => 'maintenance_mode',
            'value' => '0',
            'module' => 'app',
            'type' => 'boolean',
        ]);

        $this->assertFalse(Settings::get('maintenance_mode', 'app'));

        Settings::set('maintenance_mode', '1', 'app');

        $this->assertTrue(Settings::get('maintenance_mode', 'app'));
    }

    public function test_full_lifecycle_with_json(): void
    {
        $data = ['features' => ['dark_mode', 'notifications']];

        $this->createSetting([
            'key' => 'feature_flags',
            'value' => json_encode($data),
            'module' => 'app',
            'type' => 'json',
        ]);

        $result = Settings::get('feature_flags', 'app');

        $this->assertIsArray($result);
        $this->assertEquals($data, $result);
    }

    // -------------------------------------------------------
    //  Model::set() + Facade::get() integration
    // -------------------------------------------------------

    public function test_model_set_clears_cache_for_facade_get(): void
    {
        $this->createSetting([
            'key' => 'color',
            'value' => 'blue',
            'module' => 'general',
        ]);

        // Prime the cache via facade
        Settings::get('color', 'general');

        // Update via model static method
        Setting::set('color', 'red', 'general');

        // Facade should now return the updated value
        $result = Settings::get('color', 'general');
        $this->assertSame('red', $result);
    }

    // -------------------------------------------------------
    //  Module-level retrieval
    // -------------------------------------------------------

    public function test_module_settings_retrieval(): void
    {
        $this->createSetting(['key' => 'smtp_host', 'value' => 'smtp.example.com', 'module' => 'email', 'order' => 0]);
        $this->createSetting(['key' => 'smtp_port', 'value' => '587', 'module' => 'email', 'order' => 1]);
        $this->createSetting(['key' => 'unrelated', 'value' => 'val', 'module' => 'general']);

        $emailSettings = Settings::module('email');

        $this->assertCount(2, $emailSettings);
        $this->assertSame('smtp.example.com', $emailSettings['smtp_host']);
        $this->assertSame('587', $emailSettings['smtp_port']);
        $this->assertArrayNotHasKey('unrelated', $emailSettings);
    }

    // -------------------------------------------------------
    //  Multiple modules isolation
    // -------------------------------------------------------

    public function test_same_key_different_modules_are_isolated(): void
    {
        $this->createSetting(['key' => 'name', 'value' => 'General Name', 'module' => 'general']);
        $this->createSetting(['key' => 'name', 'value' => 'App Name', 'module' => 'app']);

        $this->assertSame('General Name', Settings::get('name', 'general'));
        $this->assertSame('App Name', Settings::get('name', 'app'));
    }

    // -------------------------------------------------------
    //  Default values
    // -------------------------------------------------------

    public function test_default_value_for_missing_setting_with_fallback(): void
    {
        $this->assertSame('fallback', Settings::get('nonexistent', 'general', 'fallback'));
    }

    public function test_default_value_for_missing_setting_returns_null(): void
    {
        $this->assertNull(Settings::get('truly_missing', 'general'));
    }

    public function test_default_value_for_inactive_setting(): void
    {
        $this->createSetting([
            'key' => 'disabled_feature',
            'value' => 'should_not_see',
            'is_active' => false,
        ]);

        $this->assertSame('default', Settings::get('disabled_feature', 'general', 'default'));
    }

    // -------------------------------------------------------
    //  Cache isolation per key
    // -------------------------------------------------------

    public function test_cache_keys_are_isolated_per_module(): void
    {
        $this->createSetting(['key' => 'timeout', 'value' => '30', 'module' => 'general']);
        $this->createSetting(['key' => 'timeout', 'value' => '60', 'module' => 'security']);

        // Both should cache independently
        $general = Settings::get('timeout', 'general');
        $security = Settings::get('timeout', 'security');

        $this->assertSame('30', $general);
        $this->assertSame('60', $security);

        // Update one doesn't affect the other
        Settings::set('timeout', '45', 'general');

        $this->assertSame('45', Settings::get('timeout', 'general'));
        $this->assertSame('60', Settings::get('timeout', 'security'));
    }
}
