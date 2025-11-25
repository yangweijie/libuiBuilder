<?php

namespace Kingbes\Libui\Declarative;

// 状态管理器 - 类似Vue的响应式系统
use Kingbes\Libui\Declarative\Components\Component;

class StateManager
{
    private static array $state = [];
    private static array $watchers = [];
    private static array $componentRefs = [];

    // 设置状态值
    public static function set(string $key, $value): void
    {
        $oldValue = self::get($key); // 使用现有的 get 方法获取原值，支持点号语法

        // 如果是点号语法，设置嵌套属性
        if (str_contains($key, '.')) {
            self::setNested($key, $value);
        } else {
            self::$state[$key] = $value;
        }

        // 触发监听器 - 注意监听器可能订阅的是完整路径
        if (isset(self::$watchers[$key])) {
            foreach (self::$watchers[$key] as $callback) {
                $callback($value, $oldValue);
            }
        }
    }

    private static function setNested(string $key, $value): void
    {
        $parts = explode('.', $key);
        $current = &self::$state;
        $lastIndex = count($parts) - 1;

        for ($i = 0; $i < $lastIndex; $i++) {
            $part = $parts[$i];
            if (!isset($current[$part]) || !is_array($current[$part])) {
                $current[$part] = [];
            }
            $current = &$current[$part];
        }

        $current[$parts[$lastIndex]] = $value;
    }

    // 获取状态值
    public static function get(string $key, $default = null)
    {
        // 支持点号语法访问嵌套属性，如 'form.username'
        if (str_contains($key, '.')) {
            $parts = explode('.', $key);
            $current = self::$state;
            foreach ($parts as $part) {
                if (is_array($current) && isset($current[$part])) {
                    $current = $current[$part];
                } else {
                    return $default;
                }
            }
            return $current;
        } else {
            return self::$state[$key] ?? $default;
        }
    }

    // 监听状态变化
    public static function watch(string $key, callable $callback): void
    {
        if (!isset(self::$watchers[$key])) {
            self::$watchers[$key] = [];
        }
        self::$watchers[$key][] = $callback;
    }

    // 注册组件引用
    public static function registerComponent(string $ref, Component $component): void
    {
        self::$componentRefs[$ref] = $component;
    }

    // 获取组件引用
    public static function getComponent(string $ref): ?Component
    {
        return self::$componentRefs[$ref] ?? null;
    }

    // 批量更新状态
    public static function batch(callable $updates): void
    {
        $updates();
    }
}