<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Spinbox;

/**
 * 数字输入框构建器
 */
class SpinboxBuilder extends ComponentBuilder
{
    /**
     * 设置范围
     *
     * @param int $min 最小值
     * @param int $max 最大值
     * @return $this
     */
    public function range(int $min, int $max): self
    {
        $this->config['min'] = $min;
        $this->config['max'] = $max;
        return $this;
    }

    /**
     * 设置初始值
     *
     * @param int $value 初始值
     * @return $this
     */
    public function value(int $value): self
    {
        $this->config['value'] = $value;
        return $this;
    }

    /**
     * 绑定到状态
     *
     * @param string $stateKey 状态键名
     * @return $this
     */
    public function bind(string $stateKey): self
    {
        $this->config['bind'] = $stateKey;
        
        // 如果有状态管理器，自动同步初始值
        if ($this->stateManager && $this->stateManager->has($stateKey)) {
            $this->config['value'] = $this->stateManager->get($stateKey);
        }
        
        return $this;
    }

    /**
     * 构建数字输入框组件
     *
     * @return CData 数字输入框句柄
     */
    protected function buildComponent(): CData
    {
        $min = $this->config['min'] ?? 0;
        $max = $this->config['max'] ?? 100;

        // 创建数字输入框
        $this->handle = Spinbox::create($min, $max);

        // 设置初始值
        if (isset($this->config['value'])) {
            Spinbox::setValue($this->handle, $this->config['value']);
        }

        return $this->handle;
    }

    /**
     * 构建后处理 - 绑定事件
     *
     * @return void
     */
    protected function afterBuild(): void
    {
        // 绑定值改变事件
        if (isset($this->events['onChange']) || isset($this->config['bind'])) {
            $callback = $this->events['onChange'] ?? null;
            $stateKey = $this->config['bind'] ?? null;
            $stateManager = $this->stateManager;
            
            Spinbox::onChanged($this->handle, function($spinbox) use ($callback, $stateKey, $stateManager) {
                $value = Spinbox::value($spinbox);
                
                // 更新状态
                if ($stateKey && $stateManager) {
                    $stateManager->set($stateKey, $value);
                }
                
                // 调用回调
                if ($callback) {
                    if ($stateManager) {
                        $callback($value, $this, $stateManager);
                    } else {
                        $callback($value, $this);
                    }
                }
            });
        }
    }

    /**
     * 获取组件类型
     *
     * @return string
     */
    public function getType(): string
    {
        return 'spinbox';
    }

    /**
     * 获取数值
     *
     * @return int
     */
    public function getValue(): int
    {
        if ($this->handle) {
            return Spinbox::value($this->handle);
        }
        return $this->config['value'] ?? 0;
    }

    /**
     * 设置数值（动态更新）
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue(mixed $value): self
    {
        $value = (int)$value;
        $this->config['value'] = $value;
        
        if ($this->handle) {
            Spinbox::setValue($this->handle, $value);
        }
        
        // 更新绑定的状态
        if (isset($this->config['bind']) && $this->stateManager) {
            $this->stateManager->set($this->config['bind'], $value);
        }
        
        return $this;
    }

    
}
