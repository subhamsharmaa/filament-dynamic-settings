<?php

namespace Subham\FilamentDynamicSettings\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Subham\FilamentDynamicSettings\Models\Setting;
use Subham\FilamentDynamicSettings\Resolvers\ComponentResolver;
use Subham\FilamentDynamicSettings\Traits\CanRegisterNavigation;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;
    use CanRegisterNavigation;
    
    public static function shouldRegisterNavigation(): bool
    {
        return self::shouldRegisterComponent('page');
    }

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected string $view = 'filament-dynamic-settings::filament.pages.manage-settings';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return config('filament-dynamic-settings.navigation.group', 'System');
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-dynamic-settings.navigation.sort', 100);
    }

    public static function isScopedToTenant(): bool
    {
        return config('filament-dynamic-settings.multi_tenant', false);
    }

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $query = Setting::active()->orderBy('order');

        if (config('filament-dynamic-settings.multi_tenant', false)) {
            $query->forTenant();
        }

        $settings = $query->get();
        $data = [];

        foreach ($settings as $setting) {
            $data[$setting->module][$setting->key] = $setting->value;
        }

        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        $layout = config('filament-dynamic-settings.layout', 'tabs');

        return $schema
            ->components(
                $layout === 'tabs' 
                    ? $this->buildTabsSchema() 
                    : $this->buildSectionsSchema()
            )
            ->statePath('data');
    }

    protected function buildTabsSchema(): array
    {
        $modules = config('filament-dynamic-settings.modules', []);
        $tabs = [];
        uasort($modules, function ($a, $b) {
            return ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0);
        });
        foreach ($modules as $moduleKey => $moduleConfig) {
            $query = Setting::byModule($moduleKey)->active()->orderBy('order');

            if (config('filament-dynamic-settings.multi_tenant', false)) {
                $query->forTenant();
            }

            $settings = $query->get();

            if ($settings->isEmpty()) {
                continue;
            }

            $components = [];
            foreach ($settings as $setting) {
                $components[] = ComponentResolver::resolve($setting);
            }

            $tabs[] = Tab::make($moduleConfig['label'])
                ->icon($moduleConfig['icon'] ?? null)
                ->schema([
                    Section::make()
                        ->schema($components)
                        ->statePath($moduleKey)
                ]);
        }

        return [
            Tabs::make('Settings')
                ->tabs($tabs)
                ->columnSpanFull()
                 ->persistTabInQueryString()
        ];
    }

    protected function buildSectionsSchema(): array
    {
        $modules = config('filament-dynamic-settings.modules', []);
        $sections = [];

        foreach ($modules as $moduleKey => $moduleConfig) {
            $query = Setting::byModule($moduleKey)->active()->orderBy('order');

            if (config('filament-dynamic-settings.multi_tenant', false)) {
                $query->forTenant();
            }

            $settings = $query->get();

            if ($settings->isEmpty()) {
                continue;
            }

            $components = [];
            foreach ($settings as $setting) {
                $components[] = ComponentResolver::resolve($setting);
            }

            $sections[] = Section::make($moduleConfig['label'])
                ->description($moduleConfig['description'] ?? null)
                ->icon($moduleConfig['icon'] ?? null)
                ->schema([
                    Section::make()
                        ->schema($components)
                        ->statePath($moduleKey)
                ]);
        }

        return $sections;
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $module => $settings) {
            foreach ($settings as $settingKey => $value) {
                Setting::set($settingKey, $value, $module);
            }
        }

        Notification::make()
            ->title(__("filament-dynamic-settings::settings.notifications.saved"))
            ->success()
            ->send();
    }
}