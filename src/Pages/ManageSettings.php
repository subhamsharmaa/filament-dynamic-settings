<?php

namespace Subham\FilamentDynamicSettings\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Subham\FilamentDynamicSettings\Models\Setting;
use Subham\FilamentDynamicSettings\Resolvers\ComponentResolver;
use Subham\FilamentDynamicSettings\Traits\CanRegisterNavigation;

class ManageSettings extends Page implements HasForms
{
    use CanRegisterNavigation;
    use InteractsWithForms;

    protected ?Collection $settings = null;
    public static function shouldRegisterNavigation(): bool
    {
        return static::shouldRegisterComponent('page');
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

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
        $this->settings = $this->getAllSettings();
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $data = [];

        foreach ($this->settings as $setting) {
            $data[$setting->module][$setting->key] = $setting->getRawValue();
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

    /**
     * Fetch all active settings in a single query, grouped by module.
     */
    protected function getAllSettings(): Collection
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $query = Setting::active()->orderBy('order');

        if (config('filament-dynamic-settings.multi_tenant', false)) {
            $query->forTenant();
        }

        return $query->get();
    }

    /**
     * Build resolved component arrays keyed by module.
     */
    protected function buildModuleSchemas(): array
    {
        $modules = config('filament-dynamic-settings.modules', []);
        $settingsByModule = $this->getAllSettings()->groupBy('module');

        uasort($modules, fn($a, $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));

        $result = [];
        foreach ($modules as $moduleKey => $moduleConfig) {
            $settings = $settingsByModule->get($moduleKey, collect());

            if ($settings->isEmpty()) {
                continue;
            }

            $components = $settings->map(
                fn(Setting $setting) => ComponentResolver::resolve($setting)
            )->all();

            $result[$moduleKey] = [
                'config' => $moduleConfig,
                'components' => $components,
            ];
        }

        return $result;
    }

    protected function buildTabsSchema(): array
    {
        $tabs = [];

        foreach ($this->buildModuleSchemas() as $moduleKey => $data) {
            $tabs[] = Tab::make($data['config']['label'])
                ->icon($data['config']['icon'] ?? null)
                ->schema([
                    Section::make()
                        ->schema($data['components'])
                        ->statePath($moduleKey),
                ]);
        }

        return [
            Tabs::make('Settings')
                ->tabs($tabs)
                ->columnSpanFull()
                ->persistTabInQueryString(),
        ];
    }

    protected function buildSectionsSchema(): array
    {
        $sections = [];

        foreach ($this->buildModuleSchemas() as $moduleKey => $data) {
            $sections[] = Section::make($data['config']['label'])
                ->description($data['config']['description'] ?? null)
                ->icon($data['config']['icon'] ?? null)
                ->schema([
                    Section::make()
                        ->schema($data['components'])
                        ->statePath($moduleKey),
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
            ->title(__('filament-dynamic-settings::settings.notifications.saved'))
            ->success()
            ->send();
    }
}