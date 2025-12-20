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
        echo "[STATEMANAGER_DEBUG] 设置状态: {$key} = '{$value}' (旧值: '{$oldValue}')\n";
        $this->state[$key] = $value;
        
        // 处理嵌套字段同步（如 formData.username 同步到 formData 数组）
        if (strpos($key, '.') !== false) {
            list($arrayKey, $field) = explode('.', $key, 2);
            if (isset($this->state[$arrayKey]) && is_array($this->state[$arrayKey])) {
                echo "[STATEMANAGER_DEBUG] 同步更新数组 {$arrayKey}.{$field} = '{$value}'\n";
                $this->state[$arrayKey][$field] = $value;
                
                // 通知数组监听者
                if (isset($this->watchers[$arrayKey])) {
                    echo "[STATEMANAGER_DEBUG] 通知数组 {$arrayKey} 的监听者\n";
                    foreach ($this->watchers[$arrayKey] as $callback) {
                        $callback($this->state[$arrayKey], $this->state[$arrayKey], $arrayKey);
                    }
                }
            }
        }
        
        echo "[STATEMANAGER_DEBUG] 状态数组现在包含: " . json_encode(array_keys($this->state)) . "\n";

        // 通知监听者
        if (isset($this->watchers[$key])) {
            echo "[STATEMANAGER_DEBUG] 通知 {$key} 的监听者，数量: " . count($this->watchers[$key]) . "\n";
            foreach ($this->watchers[$key] as $callback) {
                $callback($value, $oldValue, $key);
            }
        } else {
            echo "[STATEMANAGER_DEBUG] 没有找到 {$key} 的监听者\n";
        }
    }

    /**
     * 获取状态
     */
    public function get(string $key, $default = null)
    {
        $value = $this->state[$key] ?? $default;
        echo "[STATEMANAGER_DEBUG] 获取状态: {$key} = '{$value}'\n";
        if ($key === 'formData') {
            echo "[STATEMANAGER_DEBUG] formData详情: " . json_encode($this->state['formData'] ?? 'not found') . "\n";
        }
        return $value;
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