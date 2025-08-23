<?php

namespace Subham\FilamentDynamicSettings\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use Subham\FilamentDynamicSettings\Resources\Pages\ListSettings;
use Subham\FilamentDynamicSettings\Resources\Pages\CreateSetting;
use Subham\FilamentDynamicSettings\Resources\Pages\EditSetting;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Subham\FilamentDynamicSettings\Models\Setting;
use Subham\FilamentDynamicSettings\Resources\Pages;
use Subham\FilamentDynamicSettings\Resolvers\ComponentResolver;
use Subham\FilamentDynamicSettings\Traits\CanRegisterNavigation;

class SettingResource extends Resource
{
    use CanRegisterNavigation;
    protected static ?string $model = Setting::class;

    public static function isScopedToTenant(): bool
    {
        if (static::shouldShowAllTenants()) {
            return false;
        }
        
        return config('filament-dynamic-settings.multi_tenant', false);
    }

    /**
     * Determine if we should show all tenants (used in global panels)
     */
    protected static function shouldShowAllTenants(): bool
    {
        $currentPanel = Filament::getCurrentOrDefaultPanel();
        
        $globalPanels = config('filament-dynamic-settings.global_panels', []);
        
        return in_array($currentPanel?->getId(), $globalPanels);
    }

    protected static ?string $tenantOwnershipRelationshipName = 'tenant';

    public static function getNavigationLabel(): string
    {
        return __('filament-dynamic-settings::settings.navigation.label');
    }

    public static function getNavigationGroup(): ?string
    {
        if (static::shouldShowAllTenants()) {
            return config('filament-dynamic-settings.navigation.global_group', 'Global Management');
        }
        
        return config('filament-dynamic-settings.navigation.group', 'System');
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-dynamic-settings.navigation.sort', 100);
    }

    public static function getPluralLabel(): ?string
    {
        return __('filament-dynamic-settings::settings.labels.plural');
    }

    public static function getModelLabel(): string
    {
        return __('filament-dynamic-settings::settings.labels.singular');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::shouldRegisterComponent('resource');
    }

    public static function form(Schema $schema): Schema
    {
        $tenantField = [];

        if (config('filament-dynamic-settings.multi_tenant', false) && static::shouldShowAllTenants()) {
            $tenantModel = config('filament-dynamic-settings.tenant_model');
            $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');
            $tenantRelation = config('filament-dynamic-settings.tenant_relation', 'tenant');

            if ($tenantModel) {
                $tenantField = [
                    Select::make($tenantColumn)
                        ->label('Tenant')
                        ->relationship($tenantRelation, 'name')
                        ->searchable()
                        ->preload()
                        ->required(true),
                ];
            }
        }

        return $schema->components([
            Section::make(__('filament-dynamic-settings::settings.form.section.general'))
                ->columnSpanFull()
                ->schema([
                    ...$tenantField,

                    Select::make('module')
                        ->label(__('filament-dynamic-settings::settings.fields.module'))
                        ->options(collect(config('filament-dynamic-settings.modules', []))
                            ->mapWithKeys(fn($config, $key) => [$key => $config['label']])
                            ->toArray())
                        ->required()
                        ->searchable()
                        ->preload(),

                    TextInput::make('key')
                        ->label(__('filament-dynamic-settings::settings.fields.key'))
                        ->required()
                        ->maxLength(255)
                        ->unique(Setting::class, 'key', ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                            $rule->where('module', $get('module'));

                            if (config('filament-dynamic-settings.multi_tenant', false)) {
                                $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');
                                $rule->where($tenantColumn, $get($tenantColumn));
                            }

                            return $rule;
                        }),

                    Select::make('type')
                        ->label(__('filament-dynamic-settings::settings.fields.type'))
                        ->options(ComponentResolver::getAvailableTypes())
                        ->required()
                        ->reactive()
                        ->searchable()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (!in_array($state, ['select', 'multi_select'])) {
                                $set('options', null);
                            }
                        }),

                    TextInput::make('label')
                        ->label(__('filament-dynamic-settings::settings.fields.label'))
                        ->maxLength(255),

                    Textarea::make('description')
                        ->label(__('filament-dynamic-settings::settings.fields.description'))
                        ->maxLength(65535)
                        ->columnSpanFull(),

                    TextInput::make('order')
                        ->label(__('filament-dynamic-settings::settings.fields.order'))
                        ->numeric()
                        ->default(0),

                    KeyValue::make('options')
                        ->label(__('filament-dynamic-settings::settings.fields.options'))
                        ->columnSpanFull()
                        ->visible(fn($get) => in_array($get('type'), ['select', 'multi_select'])),

                    Section::make(__('filament-dynamic-settings::settings.form.section.validation'))
                        ->schema([
                            KeyValue::make('validation_rules')
                                ->label(__('filament-dynamic-settings::settings.fields.validation_rules'))
                                ->columnSpanFull()
                                ->keyLabel(__('filament-dynamic-settings::settings.labels.validation_rule_name'))
                                ->valueLabel(__('filament-dynamic-settings::settings.labels.validation_rule_value'))
                                ->helperText(__('filament-dynamic-settings::settings.hints.validation_rules'))
                                ->hint(__('filament-dynamic-settings::settings.hints.validation_rules_examples'))
                                ->addActionLabel(__('filament-dynamic-settings::settings.labels.add_validation_rule')),
                        ])
                        ->collapsible()
                        ->collapsed()
                        ->columns(1),

                    Toggle::make('is_active')
                        ->label(__('filament-dynamic-settings::settings.fields.is_active'))
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('module')
                ->label(__('filament-dynamic-settings::settings.fields.module'))
                ->badge()
                ->searchable()
                ->sortable(),

            TextColumn::make('key')
                ->label(__('filament-dynamic-settings::settings.fields.key'))
                ->searchable()
                ->sortable(),

            TextColumn::make('type')
                ->label(__('filament-dynamic-settings::settings.fields.type'))
                ->badge()
                ->sortable(),

            TextColumn::make('order')
                ->label(__('filament-dynamic-settings::settings.fields.order'))
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            IconColumn::make('is_active')
                ->label(__('filament-dynamic-settings::settings.fields.is_active'))
                ->boolean()
                ->sortable(),

            TextColumn::make('created_at')
                ->label(__('filament-dynamic-settings::settings.fields.created_at'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('updated_at')
                ->label(__('filament-dynamic-settings::settings.fields.updated_at'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];

        if (config('filament-dynamic-settings.multi_tenant', false) && static::shouldShowAllTenants()) {
            $tenantRelation = config('filament-dynamic-settings.tenant_relation', 'tenant');
            
            array_splice($columns, 1, 0, [
                TextColumn::make($tenantRelation . '.name')
                    ->label(__('filament-dynamic-settings::settings.fields.tenant'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
            ]);
        }

        $filters = [
            SelectFilter::make('module')
                ->label(__('filament-dynamic-settings::settings.fields.module'))
                ->options(collect(config('filament-dynamic-settings.modules', []))
                    ->mapWithKeys(fn($config, $key) => [$key => $config['label']])
                    ->toArray()),

            SelectFilter::make('type')
                ->label(__('filament-dynamic-settings::settings.fields.type'))
                ->options(ComponentResolver::getAvailableTypes()),

            TernaryFilter::make('is_active')
                ->label(__('filament-dynamic-settings::settings.fields.is_active')),
        ];

        if (static::shouldShowAllTenants()) {
            $tenantRelation = config('filament-dynamic-settings.tenant_relation', 'tenant');
            
            array_unshift($filters, 
                SelectFilter::make(config('filament-dynamic-settings.tenant_column','tenant_id'))
                    ->label(__('filament-dynamic-settings::settings.fields.tenant'))
                    ->relationship($tenantRelation, 'name')
                    ->searchable()
                    ->preload()
            );
        }

        return $table
            ->reorderable('order')
            ->defaultSort('order', 'asc')
            ->columns($columns)
            ->filters($filters)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->color('success'),
                    BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('module')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSettings::route('/'),
            'create' => CreateSetting::route('/create'),
            'edit' => EditSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (config('filament-dynamic-settings.multi_tenant', false) && 
            static::isScopedToTenant() && 
            !static::shouldShowAllTenants()) {
            $query->forTenant();
        }

        return $query;
    }
}