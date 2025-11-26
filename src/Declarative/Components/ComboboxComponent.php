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
    
    // 定义组件支持的属性
    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'editable', 'options'
        ]);
    }

    public function render(): CData
    {
        $this->isEditable = (bool)$this->getAttribute('editable', false);

        $combobox = $this->isEditable ?
            EditableCombobox::create() :
            Combobox::create();

        // 添加选项 - 处理静态选项和动态绑定的选项

        $options = $this->getAttribute('options');
        var_dump($options);
        if ($options && is_string($options)) {
            var_dump($options);

            // 静态选项，以逗号分隔

            foreach (explode(',', $options) as $option) {

                $class = $this->isEditable ?

                    EditableCombobox::class :

                    Combobox::class;

                $class::append($combobox, trim($option));

            }

        } elseif (is_array($options)) {

            // 动态绑定的选项数组（通过 :options 传入）

            foreach ($options as $option) {

                $class = $this->isEditable ?

                    EditableCombobox::class :

                    Combobox::class;

                $class::append($combobox, $option);

            }

        }

        // v-model双向绑定

        if ($model = $this->getAttribute('v-model')) {

            $initialValue = StateManager::get($model);

            if ($this->isEditable && $initialValue) {

                EditableCombobox::setText($combobox, $initialValue);

            } elseif ($initialValue >= 0) {

                Combobox::setSelected($combobox, (int)$initialValue);

            }



            // 监听特定键的变化

            StateManager::watch($model, function($newValue) use ($combobox) {

                if ($this->isEditable) {

                    EditableCombobox::setText($combobox, $newValue);

                } else {

                    Combobox::setSelected($combobox, (int)$newValue);

                }

            });



            // 如果是嵌套属性（如 form.city），还需要监听父对象的变化

            if (str_contains($model, '.')) {

                $parts = explode('.', $model);

                $parentKey = implode('.', array_slice($parts, 0, -1)); // 获取父键，如 'form'

                

                StateManager::watch($parentKey, function($newValue) use ($combobox, $model, $parts) {

                    if (is_array($newValue)) {

                        $lastPart = end($parts); // 获取最后一部分，如 'city'

                        if (isset($newValue[$lastPart])) {

                            if ($this->isEditable) {

                                EditableCombobox::setText($combobox, $newValue[$lastPart]);

                            } else {

                                Combobox::setSelected($combobox, (int)$newValue[$lastPart]);

                            }

                        }

                    }

                });

            }

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