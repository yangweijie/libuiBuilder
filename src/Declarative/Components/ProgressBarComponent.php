<?php

namespace Kingbes\Libui\Declarative\Components;

// 增强的进度条组件
use AllowDynamicProperties;
use FFI\CData;
use Kingbes\Libui\Declarative\EventBus;
use Kingbes\Libui\Declarative\StateManager;
use Kingbes\Libui\ProgressBar;

#[AllowDynamicProperties]
class ProgressBarComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:progressbar';
    }
    
    // 定义组件支持的属性
    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'value', ':value', 'auto-increment'
        ]);
    }

    public function render(): CData
    {
        $progressbar = ProgressBar::create();

        // 值绑定
        if ($value = $this->getAttribute('value')) {
            ProgressBar::setValue($progressbar, (int)$value);
        }

        // 动态值绑定
        if ($valueBinding = $this->getAttribute(':value')) {
            StateManager::watch($valueBinding, function($newValue) use ($progressbar) {
                ProgressBar::setValue($progressbar, (int)$newValue);

                // 触发进度事件
                EventBus::emit('progressbar:updated', $this->ref, $newValue);

                // 完成事件
                if ($newValue >= 100) {
                    EventBus::emit('progressbar:completed', $this->ref);
                    if (isset($this->eventHandlers['complete'])) {
                        $this->eventHandlers['complete']();
                    }
                }
            });

            $initialValue = StateManager::get($valueBinding, 0);
            ProgressBar::setValue($progressbar, $initialValue);
        }

        // 自动递增支持
        if ($autoIncrement = $this->getAttribute('auto-increment')) {
            $this->setupAutoIncrement($progressbar, (int)$autoIncrement);
        }

        $this->handle = $progressbar;
        return $progressbar;
    }

    private function setupAutoIncrement(CData $progressbar, int $interval): void
    {
        // 模拟自动递增（需要定时器支持）
        EventBus::on("progressbar:start_{$this->ref}", function() use ($progressbar, $interval) {
            // 这里需要实际的定时器实现
            // 简化版本只提供事件接口
            EventBus::emit('progressbar:auto_increment_started', $this->ref, $interval);
        });
    }

    public function getValue()
    {
        return $this->handle ? ProgressBar::value($this->handle) : 0;
    }

    public function setValue($value): void
    {
        if ($this->handle) {
            ProgressBar::setValue($this->handle, (int)$value);
        }
    }
}