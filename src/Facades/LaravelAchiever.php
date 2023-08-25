<?php

namespace Aenzenith\LaravelAchiever\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelAchiever extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-achiever';
    }
}
