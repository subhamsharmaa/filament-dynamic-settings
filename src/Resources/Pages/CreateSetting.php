<?php

namespace Subham\FilamentDynamicSettings\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Subham\FilamentDynamicSettings\Resources\SettingResource;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;
}