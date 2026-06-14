<?php

declare(strict_types=1);

namespace Pin\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array generate(string|null $rule = null, bool $dark = false)
 * @method static void validate(string $payload, string|null $rule = null)
 * @method static void verify(string $payload, string|null $rule = null)
 *
 * @see \Pin\Captcha\Captcha
 */
class Captcha extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return 'pin.captcha';
    }
}
