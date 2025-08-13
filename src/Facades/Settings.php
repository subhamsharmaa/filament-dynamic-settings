<?php

namespace Subham\FilamentDynamicSettings\Facades;

use Illuminate\Support\Facades\Facade;
class Settings extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'dynamic-settings';
    }
}