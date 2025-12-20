<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Button;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Core\Event\ButtonClickEvent;

/**
 * 按钮构建器
 */
class ButtonBuilder extends ComponentBuilder
{
    /**
     * 设置按钮文本
     *
     * @param string $text 文本
     * @return $this
     */
    public function text(string $text): self
    {
        $this->config['text'] = $text;
        return $this;
    }

    /**
     * 构建按钮
     *
     * @return CData 按钮句柄
     */
    protected function buildComponent(): CData
    {
        $text = $this->config['text'] ?? 'Button';
        
        // 创建按钮
        return Button::create($text);
    }

    /**
     * 构建后处理（绑定事件等）
     *
     * @return void
     */
    protected function afterBuild(): void
    {
        echo "[BUTTON_DEBUG] afterBuild() 被调用，按钮 ID: " . ($this->id ?? 'unknown') . "\n";
        echo "[BUTTON_DEBUG] 已注册的事件: " . json_encode(array_keys($this->events)) . "\n";
        
        // 绑定点击事件（支持事件分发器）
        // 支持 onClick 和 click 两种事件名
        $callback = null;
        $eventName = null;
        
        if (isset($this->events['onClick'])) {
            $callback = $this->events['onClick'];
            $eventName = 'onClick';
        } elseif (isset($this->events['click'])) {
            $callback = $this->events['click'];
            $eventName = 'click';
        }
        
        if ($callback) {
            echo "[BUTTON_DEBUG] 绑定 {$eventName} 事件处理器\n";
            $stateManager = $this->stateManager;
            $eventDispatcher = $this->eventDispatcher;
            
            Button::onClicked($this->handle, function($button) use ($callback, $stateManager, $eventDispatcher, $eventName) {
                echo "[BUTTON_DEBUG] === 按钮被点击了！===\n";
                echo "[BUTTON_DEBUG] 按钮 ID: " . ($this->id ?? 'unknown') . "\n";
                echo "[BUTTON_DEBUG] 事件名: {$eventName}\n";
                
                // 通过事件分发器触发（如果可用）
                if ($eventDispatcher) {
                    echo "[BUTTON_DEBUG] 触发事件分发器\n";
                    $event = new ButtonClickEvent($this, $stateManager);
                    $eventDispatcher->dispatch($event);
                } else {
                    echo "[BUTTON_DEBUG] 没有事件分发器\n";
                }
                
                // 调用传统回调 - 支持1-3个参数
                $args = [];
                $args[] = $this;  // component
                if ($stateManager) {
                    $args[] = $stateManager;
                }
                if ($eventDispatcher) {
                    $args[] = $eventDispatcher;
                }
                
                echo "[BUTTON_DEBUG] 调用回调函数，参数数量: " . count($args) . "\n";
                call_user_func_array($callback, $args);
                echo "[BUTTON_DEBUG] 回调函数调用完成\n";
            });
            
            echo "[BUTTON_DEBUG] {$eventName} 事件绑定完成\n";
        } else {
            echo "[BUTTON_DEBUG] 警告：没有找到 onClick 或 click 事件处理器\n";
        }
    }

    /**
     * 获取组件类型
     *
     * @return string
     */
    public function getType(): string
    {
        return 'button';
    }

    /**
     * 获取按钮文本
     *
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->config['text'] ?? null;
    }

    /**
     * 设置按钮文本（动态更新）
     *
     * @param string $text
     * @return $this
     */
    public function setText(string $text): self
    {
        $this->config['text'] = $text;
        
        if ($this->handle) {
            Button::setText($this->handle, $text);
        }
        
        return $this;
    }

    /**
     * 获取组件值（覆盖默认实现）
     * 
     * 对于按钮，值优先使用 text 属性
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->config['value'] ?? $this->config['text'] ?? null;
    }

    /**
     * 设置组件值（覆盖默认实现）
     * 
     * 对于按钮，如果是字符串则更新文本
     *
     * @param mixed $value
     * @return self
     */
    public function setValue(mixed $value): self
    {
        $this->config['value'] = $value;
        if (is_string($value)) {
            $this->setText($value);
        }
        return $this;
    }

    /**
     * 更新组件显示值（覆盖默认实现）
     *
     * @param mixed $value
     * @return void
     */
    protected function updateComponentValue(mixed $value): void
    {
        if (is_string($value)) {
            $this->setText($value);
        }
    }
}
