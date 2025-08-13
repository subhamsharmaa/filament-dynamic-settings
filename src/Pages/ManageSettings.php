<?php

namespace Subham\FilamentDynamicSettings\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Section;
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

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament-dynamic-settings::filament.pages.manage-settings';

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

    public function form(Form $form): Form
    {
        $layout = config('filament-dynamic-settings.layout', 'tabs');

        return $form
            ->schema(
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

            $tabs[] = Tabs\Tab::make($moduleConfig['label'])
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
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save Settings')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }
}