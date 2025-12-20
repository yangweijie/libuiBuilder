<?php

namespace Kingbes\Libui\View\State;

/**
 * 状态管理器 - 全局数据共享中心
 */
class StateManager
{
    private static ?StateManager $instance = null;
    private array $state = [];
    private array $watchers = [];
    private array $componentRefs = [];

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 设置状态
     */
    public function set(string $key, $value): void
    {
        $oldValue = $this->state[$key] ?? null;
        $this->state[$key] = $value;

        // 通知监听者
        if (isset($this->watchers[$key])) {
            foreach ($this->watchers[$key] as $callback) {
                $callback($value, $oldValue, $key);
            }
        }
    }

    /**
     * 获取状态
     */
    public function get(string $key, $default = null)
    {
        return $this->state[$key] ?? $default;
    }

    /**
     * 检查状态是否存在
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->state);
    }

    /**
     * 监听状态变化
     */
    public function watch(string $key, callable $callback): void
    {
        if (!isset($this->watchers[$key])) {
            $this->watchers[$key] = [];
        }
        $this->watchers[$key][] = $callback;
    }

    /**
     * 批量更新状态
     */
    public function update(array $updates): void
    {
        foreach ($updates as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * 注册组件引用
     */
    public function registerComponent(string $id, ComponentInterface $component): void
    {
        $this->componentRefs[$id] = new ComponentRef($id, $component);
    }

    /**
     * 获取组件引用
     */
    public function getComponent(string $id): ?ComponentRef
    {
        return $this->componentRefs[$id] ?? null;
    }

    /**
     * 获取所有状态（调试用）
     */
    public function dump(): array
    {
        return $this->state;
    }

    /**
     * 重置状态管理器（主要用于测试）
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * 清理所有状态和监听器
     */
    public function clear(): void
    {
        $this->state = [];
        $this->watchers = [];
        $this->componentRefs = [];
    }
}