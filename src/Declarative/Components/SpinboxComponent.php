<?php

namespace Kingbes\Libui\Declarative\Components;

// 增强的数字输入框组件
use FFI\CData;
use Kingbes\Libui\Declarative\EventBus;
use Kingbes\Libui\Declarative\StateManager;
use Kingbes\Libui\Spinbox;

class SpinboxComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:spinbox';
    }
    
    // 定义组件支持的属性
    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'min', 'max', 'step'
        ]);
    }

    public function render(): CData
    {
        $min = (int)$this->getAttribute('min', 0);
        $max = (int)$this->getAttribute('max', 100);
        $spinbox = Spinbox::create($min, $max);

        // v-model双向绑定
        if ($model = $this->getAttribute('v-model')) {
            $initialValue = StateManager::get($model, $min);
            Spinbox::setValue($spinbox, $initialValue);

            StateManager::watch($model, function($newValue) use ($spinbox) {
                Spinbox::setValue($spinbox, (int)$newValue);
            });
        }

        // 绑定变化事件
        Spinbox::onChanged($spinbox, function() use ($spinbox, $model) {
            $currentValue = Spinbox::value($spinbox);

            if ($model) {
                StateManager::set($model, $currentValue);
            }

            if (isset($this->eventHandlers['change'])) {
                $this->eventHandlers['change']($currentValue);
            }

            // 数值验证事件
            if (isset($this->eventHandlers['validate'])) {
                $isValid = $this->eventHandlers['validate']($currentValue);
                EventBus::emit('spinbox:validated', $this->ref, $currentValue, $isValid);
            }

            EventBus::emit('spinbox:changed', $this->ref, $currentValue);
        });

        // 步长支持（模拟）
        if ($step = $this->getAttribute('step')) {
            $this->addStepSupport($spinbox, (int)$step);
        }

        $this->handle = $spinbox;
        return $spinbox;
    }

    private function addStepSupport(CData $spinbox, int $step): void
    {
        // 模拟步长功能
        EventBus::on("spinbox:step_{$this->ref}", function($direction) use ($spinbox, $step) {
            $current = Spinbox::value($spinbox);
            $newValue = $direction === 'up' ? $current + $step : $current - $step;
            Spinbox::setValue($spinbox, $newValue);
        });
    }

    public function getValue()
    {
        return $this->handle ? Spinbox::value($this->handle) : 0;
    }

    public function setValue($value): void
    {
        if ($this->handle) {
            Spinbox::setValue($this->handle, (int)$value);
        }
    }
}