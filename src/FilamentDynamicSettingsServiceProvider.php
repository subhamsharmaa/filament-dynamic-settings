<?php

namespace Subham\FilamentDynamicSettings;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Subham\FilamentDynamicSettings\Resolvers\ComponentResolver;
use Subham\FilamentDynamicSettings\Services\SettingsManager;

class FilamentDynamicSettingsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-dynamic-settings';

    public static string $viewNamespace = 'filament-dynamic-settings';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasViews()
            ->hasConfigFile('filament-dynamic-settings')
            ->discoversMigrations()
            ->hasTranslations()
            ->hasMigration('create_settings_table')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('ssuvam-dev/filament-dynamic-settings');
            });
    }
    
    public function boot(): void
    {
        parent::boot();
        $this->registerCustomComponentResolvers();
    }

     protected function registerCustomComponentResolvers(): void
    {
        $resolvers = config('filament-dynamic-settings.component_resolvers', []);

        foreach ($resolvers as $type => $resolver) {
            if (class_exists($resolver) && method_exists($resolver, 'resolve')) {
                ComponentResolver::registerResolver($type, [$resolver, 'resolve']);
            }
        }
    }

    public function register()
    {
        parent::register();
        $this->app->singleton('dynamic-settings', function ($app) {
            return new SettingsManager();
        });
    }
}