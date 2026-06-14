<?php

declare(strict_types=1);

namespace Pin\Database;

use Pin\Database\Concerns\HasLoggingMigration;

/**
 * 日志表迁移
 */
class LoggingMigration extends Migration
{
    use HasLoggingMigration;
}
