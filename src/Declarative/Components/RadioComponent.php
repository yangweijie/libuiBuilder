<?php

namespace Kingbes\Libui\Declarative\Components;


// 增强的单选按钮组组件
use FFI\CData;
use Kingbes\Libui\Declarative\EventBus;
use Kingbes\Libui\Declarative\StateManager;
use Kingbes\Libui\Radio;

class RadioComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:radio';
    }
    
    // 定义组件支持的属性
    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'options'
        ]);
    }

    public function render(): CData
    {
        $radio = Radio::create();

        // 添加选项
        $options = $this->getAttribute('options', '');
        if ($options) {
            foreach (explode(',', $options) as $option) {
                Radio::append($radio, trim($option));
            }
        }

        // 从子组件添加选项
        foreach ($this->children as $child) {
            if ($child->getTagName() === 'ui:option') {
                $text = $child->getAttribute('text', '');
                $value = $child->getAttribute('value', $text);
                Radio::append($radio, $text);
            }
        }

        // v-model双向绑定
        if ($model = $this->getAttribute('v-model')) {
            $initialValue = StateManager::get($model, -1);
            if ($initialValue >= 0) {
                Radio::setSelected($radio, $initialValue);
            }

            StateManager::watch($model, function($newValue) use ($radio) {
                Radio::setSelected($radio, (int)$newValue);
            });
        }

        // 绑定选择事件
        Radio::onSelected($radio, function() use ($radio, $model) {
            $selected = Radio::selected($radio);

            if ($model) {
                StateManager::set($model, $selected);
            }

            if (isset($this->eventHandlers['select'])) {
                $this->eventHandlers['select']($selected);
            }

            EventBus::emit('radio:selected', $this->ref, $selected);
        });

        $this->handle = $radio;
        return $radio;
    }

    public function getValue()
    {
        return $this->handle ? Radio::selected($this->handle) : -1;
    }

    public function setValue($value): void
    {
        if ($this->handle) {
            Radio::setSelected($this->handle, (int)$value);
        }
    }
}