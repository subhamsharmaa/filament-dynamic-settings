<?php

namespace Subham\FilamentDynamicSettings\Resources\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Subham\FilamentDynamicSettings\Resources\SettingResource;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}