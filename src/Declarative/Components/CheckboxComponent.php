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
    
    // 定义组件支持的属性
    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'text', 'checked'
        ]);
    }

    public function render(): CData
    {
        $text = $this->getAttribute('text', '');
        $checkbox = Checkbox::create($text);

        if ($checked = $this->getAttribute('checked')) {
            Checkbox::setChecked($checkbox, (bool)$checked);
        }

        // 设置初始值 - 处理 v-model
        $vModel = $this->getAttribute('v-model');
        if ($vModel) {
            $initialValue = StateManager::get($vModel, false);
            Checkbox::setChecked($checkbox, (bool)$initialValue);

            // 监听状态变化并更新组件
            StateManager::watch($vModel, function($newValue) use ($checkbox) {
                Checkbox::setChecked($checkbox, (bool)$newValue);
            });

            // 如果是嵌套属性（如 form.agreeTerms），还需要监听父对象的变化
            if (str_contains($vModel, '.')) {
                $parts = explode('.', $vModel);
                $parentKey = implode('.', array_slice($parts, 0, -1)); // 获取父键，如 'form'

                StateManager::watch($parentKey, function($newValue) use ($checkbox, $vModel, $parts) {
                    if (is_array($newValue)) {
                        $lastPart = end($parts); // 获取最后一部分，如 'agreeTerms'
                        if (isset($newValue[$lastPart])) {
                            Checkbox::setChecked($checkbox, (bool)$newValue[$lastPart]);
                        }
                    }
                });
            }

            // 创建切换事件处理器，自动更新状态
            $checkboxRef = $this->getRef();
            Checkbox::onToggled($checkbox, function($checkbox) use ($vModel, $checkboxRef) {
                $isChecked = Checkbox::checked($checkbox);
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

        $this->handle = $checkbox;
        return $checkbox;
    }

    // 重写 v-model 处理方法
    protected function handleVModel(string $modelPath): void
    {
        // 由 render 方法处理，这里可以留空或提供其他逻辑
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