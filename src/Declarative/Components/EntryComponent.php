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

    public function render(): CData
    {
        $type = $this->getAttribute('type', 'text');

        $entry = match($type) {
            'password' => Entry::createPwd(),
            'search' => Entry::createSearch(),
            default => Entry::create(),
        };

        // v-model双向绑定
        if ($model = $this->getAttribute('v-model')) {
            $initialValue = StateManager::get($model, '');
            Entry::setText($entry, $initialValue);

            StateManager::watch($model, function($newValue) use ($entry) {
                Entry::setText($entry, $newValue);
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

        $this->handle = $entry;
        return $entry;
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