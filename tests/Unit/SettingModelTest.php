<?php

namespace Subham\FilamentDynamicSettings\Tests\Unit;

use Subham\FilamentDynamicSettings\Models\Setting;
use Subham\FilamentDynamicSettings\Tests\TestCase;

class SettingModelTest extends TestCase
{
    // -------------------------------------------------------
    //  CRUD Basics
    // -------------------------------------------------------

    public function test_it_can_create_a_setting(): void
    {
        $setting = $this->createSetting([
            'key' => 'app_name',
            'value' => 'My App',
            'module' => 'general',
            'type' => 'text',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'app_name',
            'value' => 'My App',
            'module' => 'general',
        ]);

        $this->assertInstanceOf(Setting::class, $setting);
    }

    public function test_it_can_update_a_setting(): void
    {
        $setting = $this->createSetting(['key' => 'site_name', 'value' => 'Old Name']);

        $setting->update(['value' => 'New Name']);

        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
            'value' => 'New Name',
        ]);
    }

    public function test_it_can_delete_a_setting(): void
    {
        $setting = $this->createSetting(['key' => 'to_delete']);

        $setting->delete();

        $this->assertDatabaseMissing('settings', ['key' => 'to_delete']);
    }

    // -------------------------------------------------------
    //  Static set() method
    // -------------------------------------------------------

    public function test_set_creates_new_setting(): void
    {
        Setting::set('new_key', 'new_value', 'general');

        $this->assertDatabaseHas('settings', [
            'key' => 'new_key',
            'value' => 'new_value',
            'module' => 'general',
        ]);
    }

    public function test_set_updates_existing_setting(): void
    {
        $this->createSetting(['key' => 'existing', 'value' => 'old']);

        Setting::set('existing', 'updated', 'general');

        $this->assertDatabaseHas('settings', [
            'key' => 'existing',
            'value' => 'updated',
        ]);
        $this->assertDatabaseCount('settings', 1);
    }

    public function test_set_with_additional_options(): void
    {
        Setting::set('opt_key', 'value', 'general', [
            'type' => 'text',
            'label' => 'My Label',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'opt_key',
            'label' => 'My Label',
            'type' => 'text',
        ]);
    }

    // -------------------------------------------------------
    //  Scopes
    // -------------------------------------------------------

    public function test_scope_by_module(): void
    {
        $this->createSetting(['key' => 'a', 'module' => 'general']);
        $this->createSetting(['key' => 'b', 'module' => 'email']);

        $results = Setting::byModule('general')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('a', $results->first()->key);
    }

    public function test_scope_active(): void
    {
        $this->createSetting(['key' => 'active_one', 'is_active' => true]);
        $this->createSetting(['key' => 'inactive_one', 'is_active' => false]);

        $results = Setting::active()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('active_one', $results->first()->key);
    }

    public function test_scope_for_tenant_is_noop_when_single_tenant(): void
    {
        config()->set('filament-dynamic-settings.multi_tenant', false);

        $this->createSetting(['key' => 'a']);
        $this->createSetting(['key' => 'b']);

        // forTenant should not filter anything in single-tenant mode
        $results = Setting::forTenant(999)->get();

        $this->assertCount(2, $results);
    }

    // -------------------------------------------------------
    //  Casts
    // -------------------------------------------------------

    public function test_options_cast_to_array(): void
    {
        $setting = $this->createSetting([
            'key' => 'select_field',
            'type' => 'select',
            'options' => ['a' => 'Alpha', 'b' => 'Beta'],
        ]);

        $setting->refresh();

        $this->assertIsArray($setting->options);
        $this->assertEquals(['a' => 'Alpha', 'b' => 'Beta'], $setting->options);
    }

    public function test_is_active_cast_to_boolean(): void
    {
        $setting = $this->createSetting(['is_active' => 1]);
        $setting->refresh();

        $this->assertIsBool($setting->is_active);
        $this->assertTrue($setting->is_active);
    }

    public function test_validation_rules_cast_to_array(): void
    {
        $setting = $this->createSetting([
            'key' => 'validated_field',
            'validation_rules' => ['required' => '', 'max' => '255'],
        ]);

        $setting->refresh();

        $this->assertIsArray($setting->validation_rules);
        $this->assertEquals(['required' => '', 'max' => '255'], $setting->validation_rules);
    }

    // -------------------------------------------------------
    //  Cache invalidation via forget()
    // -------------------------------------------------------

    public function test_forget_clears_cache_key(): void
    {
        // Manually prime the cache
        $cacheKey = 'settings::general:cache_test';
        cache()->put($cacheKey, 'cached_value');

        $this->assertEquals('cached_value', cache()->get($cacheKey));

        Setting::forget('cache_test', 'general');

        $this->assertNull(cache()->get($cacheKey));
    }

    // -------------------------------------------------------
    //  Tenant relationship guards
    // -------------------------------------------------------

    public function test_tenant_relationship_throws_when_not_multi_tenant(): void
    {
        config()->set('filament-dynamic-settings.multi_tenant', false);

        $setting = $this->createSetting();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tenant relationship is only available in multi-tenant mode.');

        $setting->tenant();
    }

    public function test_tenant_relationship_throws_when_tenant_model_not_set(): void
    {
        config()->set('filament-dynamic-settings.multi_tenant', true);
        config()->set('filament-dynamic-settings.tenant_model', null);

        $setting = $this->createSetting();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tenant model must be configured for multi-tenant mode.');

        $setting->tenant();
    }
}
