<?php

declare(strict_types=1);

namespace Pin\Errors;

/**
 * 系统错误码定义（统一错误枚举）
 *
 * 用于定义全局错误码体系，并通过 IError 提供标准化访问能力：
 * - 错误码（code）
 * - 错误信息（message）
 * - HTTP 状态码映射（statusCode）
 *
 * 该枚举作为系统错误的唯一来源
 */
enum Errors: string implements IError
{
    use Errorful;

    case None = '0|请求成功';
    case Failed = '1|请求失败';

    case BadRequest = '400|请求异常|400';
    case ModelNotFound = '404|:model不存在|404';
    case TokenMismatch = '419|页面过期，请重试或者刷新页面|419';
    case ValidateFailed = '422|提交的数据有误，请检查|422';
    case TooManyAttempts = '429|您的请求次数过多，请稍后再试|429';
    case ErrServer = '500|系统繁忙，请稍后再试|500';
    case Unauthorized = '4030|您没有权限执行该操作|403';
    case Unknown = '9999|未知错误|500';

    case CreateFailed = '1000|添加失败';
    case UpdateFailed = '1001|更新失败';
    case DeleteFailed = '1002|删除失败';
    case DataVersionMismatch = '1003|更新失败，请刷新重试';

    // auth token
    case AuthUserNotFound = '2000|请登录|401';
    case AuthTokenExpired = '2001|会话已经过期，请重新登录|401';
    case AuthTokenInvalid = '2002|登录状态异常，请重新登录|401';
    case AuthTokenMissing = '2003|请登录|401';

    // captcha
    case CaptchaMismatch = '3000|验证码错误|422';
    case CaptchaMissing = '3001|验证码错误|422';
    case CaptchaExpired = '3002|验证码已过期|422';
    case CaptchaValueInvalid = '3003|验证码错误|422';
    case CaptchaTokenInvalid = '3004|验证码错误|422';
    case CaptchaRuleInvalid = '3005|验证码错误|422';

    // csrf
    case CSRFMismatch = '4000|页面已经过期，请重试或者刷新页面|419';
    case CSRFInvalid = '4001|页面已经过期，请重试或者刷新页面|419';
    case CSRFMissing = '4002|页面已经过期，请重试或者刷新页面|419';

    // token
    case TokenExpired = '5000|凭证已过期';
    case TokenInvalid = '5001|凭证异常|500';
    case TokenMissing = '5002|凭证缺失';

    // upload
    case UploadFailed = '6000|上传失败';
    case UploadExtensionNotAllow = '6001|:attribute后缀名不允许|422';
    case UploadMimeTypeNotAllow = '6002|:attribute类型不允许|422';
    case UploadMinSizeNotAllow = '6003|:attribute大小不能小于:min|422';
    case UploadMaxSizeNotAllow = '6004|:attribute大小不能大于:max|422';

    case PasswordInvalid = '7000|密码异常|422';
    case PasswordTooShort = '7010|:attribute不能小于:min位|422';
    case PasswordTooLong = '7011|:attribute不能大于:max位|422';
    case PasswordMissingNumber = '7012|:attribute必须包含数字|422';
    case PasswordMissingLetter = '7013|:attribute必须包含字母|422';
    case PasswordMissingLowercase = '7014|:attribute必须包含小写字母|422';
    case PasswordMissingUppercase = '7015|:attribute必须包含大写字母|422';
    case PasswordMissingMixedCase = '7016|:attribute必须包含大小写字母|422';
    case PasswordMissingSymbol = '7017|:attribute必须包含特殊字符|422';
    case PasswordInsufficientTypes = '7018|:attribute至少需要包含字母、数字、特殊字符中的2种|422';
    case PasswordRequiresAllTypes = '7019|:attribute必须包含字母、数字和特殊字符|422';
    case PasswordContainsWhitespace = '7020|:attribute不能包含空格|422';
    case PasswordSequenceTooLong = '7021|:attribute不能包含连续:size位以上的顺序字母或数字|422';
    case PasswordTooManyRepeats = '7022|:attribute不能包含连续重复:size位以上的字符|422';

    /**
     * 根据错误码获取错误定义
     *
     * 未命中时返回 Unknown
     */
    public static function get(int $code): IError
    {
        return Registry::get($code);
    }

    /**
     * 获取错误消息（支持占位符替换）
     */
    public static function getMessage(int $code, array $replace = []): string
    {
        return Registry::get($code)->message($replace);
    }
}
