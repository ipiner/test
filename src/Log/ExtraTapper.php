<?php

declare(strict_types=1);

namespace Pin\Log;

use Illuminate\Log\Logger;

/**
 * 日志扩展 Tap 回调
 *
 * @codeCoverageIgnore
 */
class ExtraTapper
{
    /**
     * Tap 回调方法
     */
    public function __invoke(Logger $logger): void
    {
        $logger->getLogger()->pushProcessor(app(ExtraProcessor::class));
    }
}
