<?php

namespace Kingbes\Libui\Declarative\Components;

// 增强的滑块组件
use FFI\CData;
use Kingbes\Libui\Declarative\EventBus;
use Kingbes\Libui\Declarative\StateManager;
use Kingbes\Libui\Slider;

class SliderComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:slider';
    }

    public function render(): CData
    {
        $min = (int)$this->getAttribute('min', 0);
        $max = (int)$this->getAttribute('max', 100);
        $slider = Slider::create($min, $max);

        // 设置初始值
        $value = (int)$this->getAttribute('value', $min);
        Slider::setValue($slider, $value);

        // v-model双向绑定
        if ($model = $this->getAttribute('v-model')) {
            $initialValue = StateManager::get($model, $value);
            Slider::setValue($slider, $initialValue);

            StateManager::watch($model, function($newValue) use ($slider) {
                Slider::setValue($slider, (int)$newValue);
            });
        }

        // 绑定变化事件
        Slider::onChanged($slider, function() use ($slider, $model) {
            $currentValue = Slider::value($slider);

            if ($model) {
                StateManager::set($model, $currentValue);
            }

            // 触发自定义事件
            if (isset($this->eventHandlers['change'])) {
                $this->eventHandlers['change']($currentValue);
            }

            // 触发范围检查事件
            $min = (int)$this->getAttribute('min', 0);
            $max = (int)$this->getAttribute('max', 100);

            if (isset($this->eventHandlers['range_check'])) {
                $this->eventHandlers['range_check']($currentValue, $min, $max);
            }

            EventBus::emit('slider:changed', $this->ref, $currentValue, $min, $max);
        });

        // 动态范围更新
        if ($minBinding = $this->getAttribute(':min')) {
            StateManager::watch($minBinding, function($newMin) use ($slider) {
                // libui不支持动态范围，这里触发重建事件
                EventBus::emit('slider:range_changed', $this->ref, 'min', $newMin);
            });
        }

        if ($maxBinding = $this->getAttribute(':max')) {
            StateManager::watch($maxBinding, function($newMax) use ($slider) {
                EventBus::emit('slider:range_changed', $this->ref, 'max', $newMax);
            });
        }

        $this->handle = $slider;
        return $slider;
    }

    public function getValue()
    {
        return $this->handle ? Slider::value($this->handle) : 0;
    }

    public function setValue($value): void
    {
        if ($this->handle) {
            Slider::setValue($this->handle, (int)$value);
        }
    }
}