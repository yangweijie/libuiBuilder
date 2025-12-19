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
    public function build(): CData
    {
        $text = $this->config['text'] ?? 'Button';
        
        // 创建按钮
        $this->handle = Button::create($text);

        // 绑定点击事件（支持事件分发器）
        if (isset($this->events['onClick'])) {
            $callback = $this->events['onClick'];
            $stateManager = $this->stateManager;
            $eventDispatcher = $this->eventDispatcher;
            
            Button::onClicked($this->handle, function($button) use ($callback, $stateManager, $eventDispatcher) {
                // 通过事件分发器触发（如果可用）
                if ($eventDispatcher) {
                    $event = new ButtonClickEvent($this, $stateManager);
                    $eventDispatcher->dispatch($event);
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
                
                call_user_func_array($callback, $args);
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
}
