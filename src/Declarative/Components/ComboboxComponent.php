<?php

namespace Kingbes\Libui\Declarative\Components;

// 增强的下拉框组件
use FFI\CData;
use Kingbes\Libui\Combobox;
use Kingbes\Libui\Declarative\EventBus;
use Kingbes\Libui\Declarative\StateManager;
use Kingbes\Libui\EditableCombobox;

class ComboboxComponent extends Component
{
    private bool $isEditable;

    public function getTagName(): string
    {
        return 'ui:combobox';
    }

    public function render(): CData
    {
        $this->isEditable = (bool)$this->getAttribute('editable', false);

        $combobox = $this->isEditable ?
            EditableCombobox::create() :
            Combobox::create();

        // 添加选项
        $options = $this->getAttribute('options', '');
        if ($options) {
            foreach (explode(',', $options) as $option) {
                $class = $this->isEditable ?
                    EditableCombobox::class :
                    Combobox::class;
                $class::append($combobox, trim($option));
            }
        }

        // 动态选项绑定
        if ($optionsBinding = $this->getAttribute(':options')) {
            StateManager::watch($optionsBinding, function($newOptions) use ($combobox) {
                // 清空现有选项（需要libui支持）
                // 重新添加选项
                foreach ($newOptions as $option) {
                    $class = $this->isEditable ?
                        EditableCombobox::class :
                        Combobox::class;
                    $class::append($combobox, $option);
                }
            });
        }

        // v-model双向绑定
        if ($model = $this->getAttribute('v-model')) {
            $initialValue = StateManager::get($model);
            if ($this->isEditable && $initialValue) {
                EditableCombobox::setText($combobox, $initialValue);
            } elseif ($initialValue >= 0) {
                Combobox::setSelected($combobox, (int)$initialValue);
            }

            StateManager::watch($model, function($newValue) use ($combobox) {
                if ($this->isEditable) {
                    EditableCombobox::setText($combobox, $newValue);
                } else {
                    Combobox::setSelected($combobox, (int)$newValue);
                }
            });
        }

        // 绑定选择/改变事件
        if ($this->isEditable) {
            EditableCombobox::onChanged($combobox, function() use ($combobox, $model) {
                $text = EditableCombobox::text($combobox);

                if ($model) {
                    StateManager::set($model, $text);
                }

                if (isset($this->eventHandlers['change'])) {
                    $this->eventHandlers['change']($text);
                }

                EventBus::emit('combobox:changed', $this->ref, $text);
            });
        } else {
            Combobox::onSelected($combobox, function() use ($combobox, $model) {
                $selected = Combobox::selected($combobox);

                if ($model) {
                    StateManager::set($model, $selected);
                }

                if (isset($this->eventHandlers['select'])) {
                    $this->eventHandlers['select']($selected);
                }

                EventBus::emit('combobox:selected', $this->ref, $selected);
            });
        }

        $this->handle = $combobox;
        return $combobox;
    }

    public function getValue()
    {
        if (!$this->handle) return null;

        return $this->isEditable ?
            EditableCombobox::text($this->handle) :
            Combobox::selected($this->handle);
    }

    public function setValue($value): void
    {
        if (!$this->handle) return;

        if ($this->isEditable) {
            EditableCombobox::setText($this->handle, (string)$value);
        } else {
            Combobox::setSelected($this->handle, (int)$value);
        }
    }
}