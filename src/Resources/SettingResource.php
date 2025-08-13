<?php

namespace Subham\FilamentDynamicSettings\Resources;

use Filament\Forms;
use Filament\Forms\Form;
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

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function isScopedToTenant(): bool
    {
        return config('filament-dynamic-settings.multi_tenant', false);
    }

    protected static ?string $tenantOwnershipRelationshipName = 'tenant';

    public static function getNavigationLabel(): string
    {
        return __('filament-dynamic-settings::settings.navigation.label');
    }

    public static function getNavigationGroup(): ?string
    {
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

    public static function form(Form $form): Form
    {
        $tenantField = [];

        if (config('filament-dynamic-settings.multi_tenant', false) && static::canManageMultipleTenants()) {
            $tenantModel = config('filament-dynamic-settings.tenant_model');
            $tenantColumn = config('filament-dynamic-settings.tenant_column', 'tenant_id');

            if ($tenantModel) {
                $tenantField = [
                    Forms\Components\Select::make($tenantColumn)
                        ->label('Tenant')
                        ->relationship('tenant', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(!static::isScopedToTenant()),
                ];
            }
        }

        return $form->schema([
            Forms\Components\Section::make(__('filament-dynamic-settings::settings.form.section.general'))
                ->schema([
                    ...$tenantField,

                    Forms\Components\Select::make('module')
                        ->label(__('filament-dynamic-settings::settings.fields.module'))
                        ->options(collect(config('filament-dynamic-settings.modules', []))
                            ->mapWithKeys(fn($config, $key) => [$key => $config['label']])
                            ->toArray())
                        ->required()
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('key')
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

                    Forms\Components\Select::make('type')
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

                    Forms\Components\TextInput::make('label')
                        ->label(__('filament-dynamic-settings::settings.fields.label'))
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label(__('filament-dynamic-settings::settings.fields.description'))
                        ->maxLength(65535)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('order')
                        ->label(__('filament-dynamic-settings::settings.fields.order'))
                        ->numeric()
                        ->default(0),

                    Forms\Components\KeyValue::make('options')
                        ->label(__('filament-dynamic-settings::settings.fields.options'))
                        ->columnSpanFull()
                        ->visible(fn($get) => in_array($get('type'), ['select', 'multi_select'])),

                    Forms\Components\Section::make(__('filament-dynamic-settings::settings.form.section.validation'))
                        ->schema([
                            Forms\Components\KeyValue::make('validation_rules')
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

                    Forms\Components\Toggle::make('is_active')
                        ->label(__('filament-dynamic-settings::settings.fields.is_active'))
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        $columns = [
            Tables\Columns\TextColumn::make('module')
                ->label(__('filament-dynamic-settings::settings.fields.module'))
                ->badge()
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('key')
                ->label(__('filament-dynamic-settings::settings.fields.key'))
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('type')
                ->label(__('filament-dynamic-settings::settings.fields.type'))
                ->badge()
                ->sortable(),

            Tables\Columns\TextColumn::make('order')
                ->label(__('filament-dynamic-settings::settings.fields.order'))
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\IconColumn::make('is_active')
                ->label(__('filament-dynamic-settings::settings.fields.is_active'))
                ->boolean()
                ->sortable(),

            Tables\Columns\TextColumn::make('created_at')
                ->label(__('filament-dynamic-settings::settings.fields.created_at'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('updated_at')
                ->label(__('filament-dynamic-settings::settings.fields.updated_at'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];

        if (config('filament-dynamic-settings.multi_tenant', false) && !static::isScopedToTenant()) {
            array_splice($columns, 1, 0, [
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
            ]);
        }

        return $table
            ->reorderable('order')
            ->defaultSort('order', 'asc')
            ->columns($columns)
            ->filters([
                Tables\Filters\SelectFilter::make('module')
                    ->label(__('filament-dynamic-settings::settings.fields.module'))
                    ->options(collect(config('filament-dynamic-settings.modules', []))
                        ->mapWithKeys(fn($config, $key) => [$key => $config['label']])
                        ->toArray()),

                Tables\Filters\SelectFilter::make('type')
                    ->label(__('filament-dynamic-settings::settings.fields.type'))
                    ->options(ComponentResolver::getAvailableTypes()),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('filament-dynamic-settings::settings.fields.is_active')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->color('success'),
                    Tables\Actions\BulkAction::make('deactivate')
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (config('filament-dynamic-settings.multi_tenant', false) && static::isScopedToTenant()) {
            $query->forTenant();
        }

        return $query;
    }
}
