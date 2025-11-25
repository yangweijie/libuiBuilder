<?php

namespace Kingbes\Libui\Declarative;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class LoggerManager
{
    private static ?Logger $logger = null;

    public static function getLogger(): Logger
    {
        if (self::$logger === null) {
            self::$logger = new Logger('libuiBuilder');

            // 创建 logs 目录（如果不存在）
            $logDir = __DIR__ . '/../../logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // 按天分割的日志处理器
            $rotatingHandler = new RotatingFileHandler(
                $logDir . '/error.log',
                0, // 保留所有文件
                Logger::ERROR,
                true,
                0666
            );

            $formatter = new LineFormatter(
                "[%datetime%] %level_name%: %message% %context%\n",
                "Y-m-d H:i:s",
                true, // 允许换行
                true  // 调试信息
            );
            $rotatingHandler->setFormatter($formatter);
            self::$logger->pushHandler($rotatingHandler);
        }

        return self::$logger;
    }

    // 便捷的日志记录方法
    public static function info(string $message, array $context = []): void
    {
        self::getLogger()->info($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::getLogger()->error($message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::getLogger()->debug($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::getLogger()->warning($message, $context);
    }

    // 专门用于组件错误的日志方法
    public static function componentError(string $component, string $ref, string $message, array $context = []): void
    {
        $context['component'] = $component;
        $context['ref'] = $ref;
        self::getLogger()->error($message, $context);
    }

    // 专门用于事件错误的日志方法
    public static function eventError(string $event, string $component, string $ref, string $message, array $context = []): void
    {
        $context['event'] = $event;
        $context['component'] = $component;
        $context['ref'] = $ref;
        self::getLogger()->error($message, $context);
    }
}