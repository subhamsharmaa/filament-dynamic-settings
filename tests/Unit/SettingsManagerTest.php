<?php

namespace Subham\FilamentDynamicSettings\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Subham\FilamentDynamicSettings\Facades\Settings;
use Subham\FilamentDynamicSettings\Models\Setting;
use Subham\FilamentDynamicSettings\Services\SettingsManager;
use Subham\FilamentDynamicSettings\Tests\TestCase;

class SettingsManagerTest extends TestCase
{
    protected SettingsManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = app('dynamic-settings');
    }

    // -------------------------------------------------------
    //  get()
    // -------------------------------------------------------

    public function test_get_returns_formatted_value(): void
    {
        $this->createSetting(['key' => 'count', 'value' => '42', 'type' => 'number']);

        $result = $this->manager->get('count', 'general');

        $this->assertSame(42, $result);
    }

    public function test_get_returns_default_when_not_found(): void
    {
        $result = $this->manager->get('nonexistent', 'general', 'fallback');

        $this->assertSame('fallback', $result);
    }

    public function test_get_ignores_inactive_settings(): void
    {
        $this->createSetting([
            'key' => 'disabled',
            'value' => 'should_not_return',
            'is_active' => false,
        ]);

        $result = $this->manager->get('disabled', 'general', 'default');

        $this->assertSame('default', $result);
    }

    public function test_get_caches_the_value(): void
    {
        $this->createSetting(['key' => 'cached_key', 'value' => 'cached_value']);

        // First call — hits the DB
        $result1 = $this->manager->get('cached_key', 'general');
        $this->assertSame('cached_value', $result1);

        // Manually update the DB to prove cache is used
        Setting::where('key', 'cached_key')->update(['value' => 'db_updated']);

        // Second call — should return cached value (not DB)
        $result2 = $this->manager->get('cached_key', 'general');
        $this->assertSame('cached_value', $result2);
    }

    public function test_get_respects_module_scope(): void
    {
        $this->createSetting(['key' => 'theme', 'value' => 'dark', 'module' => 'app']);
        $this->createSetting(['key' => 'theme', 'value' => 'light', 'module' => 'general']);

        $this->assertSame('dark', $this->manager->get('theme', 'app'));
        $this->assertSame('light', $this->manager->get('theme', 'general'));
    }

    // -------------------------------------------------------
    //  raw()
    // -------------------------------------------------------

    public function test_raw_returns_unformatted_value(): void
    {
        $this->createSetting(['key' => 'num', 'value' => '42', 'type' => 'number']);

        $result = $this->manager->raw('num', 'general');

        // raw() should return the string, not an int
        $this->assertSame('42', $result);
    }

    public function test_raw_returns_default_when_not_found(): void
    {
        $result = $this->manager->raw('missing', 'general', 'raw_default');

        $this->assertSame('raw_default', $result);
    }

    public function test_raw_ignores_inactive_settings(): void
    {
        $this->createSetting([
            'key' => 'inactive_raw',
            'value' => 'hidden',
            'is_active' => false,
        ]);

        $result = $this->manager->raw('inactive_raw', 'general', 'default');

        $this->assertSame('default', $result);
    }

    // -------------------------------------------------------
    //  set()
    // -------------------------------------------------------

    public function test_set_updates_existing_setting(): void
    {
        $this->createSetting(['key' => 'updatable', 'value' => 'old_value']);

        $result = $this->manager->set('updatable', 'new_value', 'general');

        $this->assertTrue($result);
        $this->assertDatabaseHas('settings', [
            'key' => 'updatable',
            'value' => 'new_value',
        ]);
    }

    public function test_set_returns_false_for_nonexistent_key(): void
    {
        $result = $this->manager->set('does_not_exist', 'value', 'general');

        $this->assertFalse($result);
    }

    public function test_set_invalidates_cache(): void
    {
        $this->createSetting(['key' => 'cache_inv', 'value' => 'original']);

        // Prime the cache
        $this->manager->get('cache_inv', 'general');

        // Update via set()
        $this->manager->set('cache_inv', 'updated', 'general');

        // get() should now return the updated value (cache was cleared)
        $result = $this->manager->get('cache_inv', 'general');
        $this->assertSame('updated', $result);
    }

    // -------------------------------------------------------
    //  module()
    // -------------------------------------------------------

    public function test_module_returns_all_settings_for_module(): void
    {
        $this->createSetting(['key' => 'key_a', 'value' => 'val_a', 'module' => 'app', 'order' => 1]);
        $this->createSetting(['key' => 'key_b', 'value' => 'val_b', 'module' => 'app', 'order' => 2]);
        $this->createSetting(['key' => 'other', 'value' => 'val_c', 'module' => 'general']);

        $result = $this->manager->module('app');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame('val_a', $result['key_a']);
        $this->assertSame('val_b', $result['key_b']);
    }

    public function test_module_excludes_inactive_settings(): void
    {
        $this->createSetting(['key' => 'active', 'value' => 'yes', 'module' => 'email', 'is_active' => true]);
        $this->createSetting(['key' => 'hidden', 'value' => 'no', 'module' => 'email', 'is_active' => false]);

        $result = $this->manager->module('email');

        $this->assertCount(1, $result);
        $this->assertSame('yes', $result['active']);
    }

    public function test_module_returns_empty_array_when_no_settings(): void
    {
        $result = $this->manager->module('nonexistent_module');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // -------------------------------------------------------
    //  Facade
    // -------------------------------------------------------

    public function test_facade_resolves_to_settings_manager(): void
    {
        $this->assertInstanceOf(SettingsManager::class, Settings::getFacadeRoot());
    }

    public function test_facade_get_works(): void
    {
        $this->createSetting(['key' => 'facade_key', 'value' => 'facade_value']);

        $result = Settings::get('facade_key', 'general');

        $this->assertSame('facade_value', $result);
    }

    // -------------------------------------------------------
    //  Singleton binding
    // -------------------------------------------------------

    public function test_settings_manager_is_a_singleton(): void
    {
        $instance1 = app('dynamic-settings');
        $instance2 = app('dynamic-settings');

        $this->assertSame($instance1, $instance2);
    }
}
