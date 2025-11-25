<?php

namespace Kingbes\Libui\Declarative;

use \FFI\CData;
// 事件总线 - 用于组件间通信
class EventBus
{
    private static array $listeners = [];

    public static function on(string $event, callable $callback): void
    {
        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = [];
        }
        self::$listeners[$event][] = $callback;
    }

    public static function emit(string $event, ...$args): void
    {
        if (isset(self::$listeners[$event])) {
            foreach (self::$listeners[$event] as $callback) {
                $callback(...$args);
            }
        }
    }

    public static function off(string $event, ?callable $callback = null): void
    {
        if (!isset(self::$listeners[$event])) return;

        if ($callback === null) {
            unset(self::$listeners[$event]);
        } else {
            self::$listeners[$event] = array_filter(
                self::$listeners[$event],
                fn($listener) => $listener !== $callback
            );
        }
    }
}