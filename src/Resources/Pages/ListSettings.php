<?php

namespace Subham\FilamentDynamicSettings\Resources\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Subham\FilamentDynamicSettings\Resources\SettingResource ;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}