<?php

namespace Kingbes\Libui\Declarative;

// 全局错误和异常处理器
use Throwable;

class ErrorHandler
{
    public static function register(): void
    {
        // 注册异常处理器
        set_exception_handler([self::class, 'handleException']);
        
        // 注册错误处理器
        set_error_handler([self::class, 'handleError']);
    }

    public static function handleException(Throwable $exception): void
    {
        $errorInfo = [
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        // 使用日志管理器记录异常
        LoggerManager::error("Uncaught Exception: " . $exception->getMessage(), $errorInfo);
        error_log("Uncaught Exception: " . $exception->getMessage());
    }

    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        $errorTypes = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        ];

        $errorType = $errorTypes[$errno] ?? 'UNKNOWN';

        $errorInfo = [
            'type' => $errorType,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        // 使用日志管理器记录错误
        LoggerManager::error("PHP Error: $errstr", $errorInfo);
        error_log("PHP Error [{$errorType}]: {$errstr} in {$errfile} on line {$errline}");

        return true; // 表示错误已被处理
    }
}