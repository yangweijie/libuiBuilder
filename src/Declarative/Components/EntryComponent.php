<?php

namespace Kingbes\Libui\Declarative\Components;

use FFI\CData;
use Kingbes\Libui\Declarative\EventBus;
use Kingbes\Libui\Declarative\StateManager;
use Kingbes\Libui\Entry;

class EntryComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:entry';
    }
    
    // 定义组件支持的属性
    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'text', 'type', 'placeholder'
        ]);
    }

    public function render(): CData
    {
        $type = $this->getAttribute('type', 'text');

        $entry = match($type) {
            'password' => Entry::createPwd(),
            'search' => Entry::createSearch(),
            default => Entry::create(),
        };

        // 设置初始文本
        $initialText = $this->getAttribute('text', '');
        if ($initialText) {
            Entry::setText($entry, $initialText);
        }

        // v-model双向绑定
        $model = $this->getAttribute('v-model');
        if ($model) {
            // 设置初始值
            $initialValue = StateManager::get($model, '');
            Entry::setText($entry, $initialValue);

            // 监听状态变化并更新组件
            StateManager::watch($model, function($newValue) use ($entry) {
                Entry::setText($entry, (string)$newValue);
            });

            // 如果是嵌套属性（如 form.username），还需要监听父对象的变化
            if (str_contains($model, '.')) {
                $parts = explode('.', $model);
                $parentKey = implode('.', array_slice($parts, 0, -1)); // 获取父键，如 'form'

                StateManager::watch($parentKey, function($newValue) use ($entry, $model, $parts) {
                    if (is_array($newValue)) {
                        $lastPart = end($parts); // 获取最后一部分，如 'username'
                        if (isset($newValue[$lastPart])) {
                            Entry::setText($entry, (string)$newValue[$lastPart]);
                        }
                    }
                });
            }

            // 修正 change 事件绑定
            Entry::onChanged($entry, function() use ($entry, $model) {
                $currentValue = Entry::text($entry);

                // 更新v-model
                if ($model) {
                    StateManager::set($model, $currentValue);
                }

                // 使用增强的事件执行
                $result = $this->executeEventHandler('change', $currentValue);

                // 触发全局事件
                EventBus::emit('entry:changed', $this->ref, $currentValue, $result);

                // 调试输出
                error_log("Entry changed: ref={$this->ref}, value={$currentValue}, result=" . print_r($result, true));
            });
        } else {
            // 修正 change 事件绑定 (没有 v-model 时)
            Entry::onChanged($entry, function() use ($entry, $model) {
                $currentValue = Entry::text($entry);

                // 使用增强的事件执行
                $result = $this->executeEventHandler('change', $currentValue);

                // 触发全局事件
                EventBus::emit('entry:changed', $this->ref, $currentValue, $result);

                // 调试输出
                error_log("Entry changed: ref={$this->ref}, value={$currentValue}, result=" . print_r($result, true));
            });
        }

        $this->handle = $entry;
        return $entry;
    }

    // 重写 v-model 处理方法
    protected function handleVModel(string $modelPath): void
    {
        // 由 render 方法处理，这里可以留空或提供其他逻辑
    }

    public function getValue()
    {
        return $this->handle ? Entry::text($this->handle) : '';
    }

    public function setValue($value): void
    {
        if ($this->handle) {
            Entry::setText($this->handle, (string)$value);
        }
    }
}