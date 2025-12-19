<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Slider;

/**
 * 滑块构建器
 */
class SliderBuilder extends ComponentBuilder
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
     * 构建滑块
     *
     * @return CData 滑块句柄
     */
    public function build(): CData
    {
        $min = $this->config['min'] ?? 0;
        $max = $this->config['max'] ?? 100;

        // 创建滑块
        $this->handle = Slider::create($min, $max);

        // 设置初始值
        if (isset($this->config['value'])) {
            Slider::setValue($this->handle, $this->config['value']);
        }

        // 绑定值改变事件
        if (isset($this->events['onChange']) || isset($this->config['bind'])) {
            $callback = $this->events['onChange'] ?? null;
            $stateKey = $this->config['bind'] ?? null;
            $stateManager = $this->stateManager;
            
            Slider::onChanged($this->handle, function($slider) use ($callback, $stateKey, $stateManager) {
                $value = Slider::value($slider);
                
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

        // 注册到状态管理器
        if ($this->id && $this->stateManager) {
            $this->stateManager->registerComponent($this->id, $this);
        }

        return $this->handle;
    }

    /**
     * 获取组件类型
     *
     * @return string
     */
    public function getType(): string
    {
        return 'slider';
    }

    /**
     * 获取滑块值
     *
     * @return int
     */
    public function getValue(): int
    {
        if ($this->handle) {
            return Slider::value($this->handle);
        }
        return $this->config['value'] ?? 0;
    }

    /**
     * 设置滑块值（动态更新）
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue(mixed $value): self
    {
        $value = (int)$value;
        $this->config['value'] = $value;
        
        if ($this->handle) {
            Slider::setValue($this->handle, $value);
        }
        
        // 更新绑定的状态
        if (isset($this->config['bind']) && $this->stateManager) {
            $this->stateManager->set($this->config['bind'], $value);
        }
        
        return $this;
    }
}
