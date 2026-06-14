<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Pin\Auth\Access\Contracts\AccessUser;
use Pin\Auth\Access\Models\Menu;

/**
 * @method static \Pin\Auth\Access\Access forUser(AccessUser $user)
 * @method static string[] codes()
 * @method static Menu[] menus()
 *
 * @see \Pin\Auth\Access\Access
 */
class Access extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return 'pin.access';
    }
}
