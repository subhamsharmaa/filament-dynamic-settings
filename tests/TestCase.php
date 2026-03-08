<?php

namespace Subham\FilamentDynamicSettings\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Subham\FilamentDynamicSettings\FilamentDynamicSettingsServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            FilamentDynamicSettingsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Load the default package config
        $app['config']->set('filament-dynamic-settings', require __DIR__.'/../config/filament-dynamic-settings.php');
    }

    /**
     * Helper to create a setting record in the database.
     */
    protected function createSetting(array $attributes = []): \Subham\FilamentDynamicSettings\Models\Setting
    {
        return \Subham\FilamentDynamicSettings\Models\Setting::create(array_merge([
            'module' => 'general',
            'key' => 'test_key',
            'value' => 'test_value',
            'type' => 'text',
            'is_active' => true,
            'order' => 0,
        ], $attributes));
    }
}
