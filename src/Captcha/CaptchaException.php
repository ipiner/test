<?php

declare(strict_types=1);

namespace Pin\Captcha;

use Pin\Errors\Errors;
use Pin\Errors\IError;
use Pin\Exceptions\Exception;
use Throwable;

/**
 * 验证码校验异常
 */
class CaptchaException extends Exception
{
    public function __construct(?IError $err = null, ?Throwable $previous = null)
    {
        parent::__construct($err ?: Errors::CaptchaMismatch, 0, $previous);
        $this->withStatusCode(422)->withErrorMessage(Errors::CaptchaMismatch->message());
    }
}
