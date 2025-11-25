<?php

namespace Kingbes\Libui\Declarative\Components;

use FFI\CData;
use Kingbes\Libui\Checkbox;
use Kingbes\Libui\Declarative\StateManager;

class CheckboxComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:checkbox';
    }

    public function render(): CData
    {
        $text = $this->getAttribute('text', '');
        $checkbox = Checkbox::create($text);

        if ($checked = $this->getAttribute('checked')) {
            Checkbox::setChecked($checkbox, (bool)$checked);
        }

        // 处理 v-model 绑定
        $vModel = $this->getAttribute('v-model');
        if ($vModel) {
            // 设置初始值
            $initialValue = StateManager::get($vModel, false);
            Checkbox::setChecked($checkbox, (bool)$initialValue);
            
            // 创建切换事件处理器，自动更新状态
            $checkboxRef = $this->getRef();
            Checkbox::onToggled($checkbox, function($checkbox) use ($vModel, $checkboxRef) {
                $isChecked = Checkbox::checked($checkbox);
                var_dump('isChecked', $isChecked);
                var_dump($vModel);
                StateManager::set($vModel, $isChecked);
                
                // 如果有额外的 on-toggle 事件处理器，也执行它
                $originalOnToggle = $this->getAttribute('on-toggle');
                if ($originalOnToggle && is_callable($originalOnToggle)) {
                    $originalOnToggle($checkbox);
                }
            });
        } else {
            // 如果没有 v-model，处理普通的 on-toggle 事件
            if ($onToggle = $this->getAttribute('on-toggle')) {
                Checkbox::onToggled($checkbox, $onToggle);
            }
        }

        return $checkbox;
    }

    public function getValue()
    {
        return Checkbox::checked($this->getHandle());
    }

    public function setValue($value): void
    {
        Checkbox::setChecked($this->getHandle(), $value);
        // 如果有 v-model，也更新状态管理器
        $vModel = $this->getAttribute('v-model');
        if ($vModel) {
            StateManager::set($vModel, $value);
        }
    }
}