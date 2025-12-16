<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\EditableCombobox;
use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Combobox;
use FFI\CData;

class ComboboxBuilder extends ComponentBuilder
{
    public function getDefaultConfig(): array
    {
        return [
            'items' => [],
            'selected' => -1,
            'placeholder' => '请选择...',
            'editable' => false,
            'onSelected' => null,
            'onChange' => null,
        ];
    }

    protected function createNativeControl(): CData
    {
        return $this->getConfig('editable')
            ? EditableCombobox::create()
            : Combobox::create();
    }

    protected function applyConfig(): void
    {
        // 添加选项
        $items = $this->getConfig('items');
        foreach ($items as $item) {
            $text = is_array($item)? $item['text'] ?? $item['value']: (string) $item;
            $this->getConfig('editable')?
                EditableCombobox::append($this->handle, $text):
                Combobox::append($this->handle, $text);
        }

        // 设置默认选中
        $selected = $this->getConfig('selected');
        if ($selected >= 0 && $selected < count($items)) {
            if ($this->getConfig('editable')) {
                // For editable combobox, we can't set selected index directly
                // Instead, we set the text if the index is valid
                $item = $items[$selected] ?? null;
                $text = is_array($item) ? $item['text'] ?? $item['value'] : (string) $item;
                EditableCombobox::setText($this->handle, $text);
            } else {
                Combobox::setSelected($this->handle, $selected);
            }
        }

        // 绑定选择事件
        if ($this->getConfig('editable')) {
            // For editable combobox, we use onChanged instead of onSelected
            EditableCombobox::onChanged($this->handle, function() use ($items) {
                $text = EditableCombobox::text($this->handle);
                
                // Find the selected index by matching text
                $newIndex = -1;
                foreach ($items as $i => $item) {
                    $itemText = is_array($item) ? $item['text'] ?? $item['value'] : (string) $item;
                    if ($itemText === $text) {
                        $newIndex = $i;
                        break;
                    }
                }
                
                $oldIndex = $this->getConfig('selected');
                
                $this->setConfig('selected', $newIndex);

                $selectedItem = $items[$newIndex] ?? null;
                $this->emit('change', $newIndex, $selectedItem, $oldIndex);

                // 用户回调
                if ($onSelected = $this->getConfig('onSelected')) {
                    $itemText = is_array($selectedItem) ? $selectedItem['text'] ?? $selectedItem['value'] : $selectedItem;
                    $onSelected($newIndex, $itemText, $this);
                }

                if ($onChange = $this->getConfig('onChange')) {
                    $itemText = is_array($selectedItem) ? $selectedItem['text'] ?? $selectedItem['value'] : $selectedItem;
                    $onChange($newIndex, $itemText, $this);
                }
            });
        } else {
            Combobox::onSelected($this->handle, function() use ($items) {
                $newIndex = Combobox::selected($this->handle);
                $oldIndex = $this->getConfig('selected');

                $this->setConfig('selected', $newIndex);

                $selectedItem = $items[$newIndex] ?? null;
                $this->emit('change', $newIndex, $selectedItem, $oldIndex);

                // 用户回调
                if ($onSelected = $this->getConfig('onSelected')) {
                    $itemText = is_array($selectedItem) ? $selectedItem['text'] ?? $selectedItem['value'] : $selectedItem;
                    $onSelected($newIndex, $itemText, $this);
                }

                if ($onChange = $this->getConfig('onChange')) {
                    $itemText = is_array($selectedItem) ? $selectedItem['text'] ?? $selectedItem['value'] : $selectedItem;
                    $onChange($newIndex, $itemText, $this);
                }
            });
        }
    }

    public function getValue()
    {
        if (!$this->handle) {
            return $this->getConfig('selected', -1);
        }

        $items = $this->getConfig('items');
        
        if ($this->getConfig('editable')) {
            $text = EditableCombobox::text($this->handle);
            
            // Find the selected index by matching text
            $index = -1;
            foreach ($items as $i => $item) {
                $itemText = is_array($item) ? $item['text'] ?? $item['value'] : (string) $item;
                if ($itemText === $text) {
                    $index = $i;
                    break;
                }
            }
            
            return [
                'index' => $index,
                'text' => $text, // For editable comboboxes, text is more important
                'item' => $items[$index] ?? null,
                'value' => $index >= 0 ? ($items[$index]['value'] ?? $items[$index]) : $text
            ];
        } else {
            $index = Combobox::selected($this->handle);
            
            return [
                'index' => $index,
                'item' => $items[$index] ?? null,
                'value' => $index >= 0 ? ($items[$index]['value'] ?? $items[$index]) : null
            ];
        }
    }

    public function setValue($value): void
    {
        $items = $this->getConfig('items');
        $index = -1;
        $text = '';

        if (is_int($value)) {
            // 按索引设置
            $index = $value;
            if ($index >= 0 && $index < count($items)) {
                $item = $items[$index];
                $text = is_array($item) ? $item['text'] ?? $item['value'] : (string) $item;
            }
        } else {
            // 按值或文本查找
            foreach ($items as $i => $item) {
                $itemValue = is_array($item) ? $item['value'] : $item;
                $itemText = is_array($item) ? $item['text'] ?? $item['value'] : (string) $item;
                
                if ($itemValue == $value || $itemText == $value) {
                    $index = $i;
                    $text = $itemText;
                    break;
                }
            }
        }

        $this->setConfig('selected', $index);
        if ($this->handle) {
            if ($this->getConfig('editable')) {
                // For editable combobox, set the text
                EditableCombobox::setText($this->handle, $text);
            } else {
                if ($index >= 0) {
                    Combobox::setSelected($this->handle, $index);
                }
            }
        }
    }

    // 链式配置方法
    public function items(array $items): static
    {
        return $this->setConfig('items', $items);
    }

    public function addItem(string $text, $value = null): static
    {
        $items = $this->getConfig('items');
        $items[] = $value !== null ? ['text' => $text, 'value' => $value] : $text;
        return $this->setConfig('items', $items);
    }

    public function selected(int $index): static
    {
        $this->setConfig('selected', $index);
        
        // If handle exists and we're setting selected after creation, update the UI
        if ($this->handle) {
            $items = $this->getConfig('items');
            if ($index >= 0 && $index < count($items)) {
                $item = $items[$index];
                $text = is_array($item) ? $item['text'] ?? $item['value'] : (string) $item;
                
                if ($this->getConfig('editable')) {
                    EditableCombobox::setText($this->handle, $text);
                } else {
                    Combobox::setSelected($this->handle, $index);
                }
            }
        }
        
        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        return $this->setConfig('placeholder', $placeholder);
    }

    public function editable(bool $editable = true): static
    {
        return $this->setConfig('editable', $editable);
    }

    public function onSelected(callable $callback): static
    {
        return $this->setConfig('onSelected', $callback);
    }

    public function clear(): static
    {
        return $this->setConfig('items', [])->setConfig('selected', -1);
    }

    public function removeItem(int $index): static
    {
        $items = $this->getConfig('items');
        if (isset($items[$index])) {
            array_splice($items, $index, 1);
            $this->setConfig('items', $items);

            // 调整选中索引
            $selected = $this->getConfig('selected');
            if ($selected >= $index) {
                $this->setConfig('selected', max(-1, $selected - 1));
            }
        }
        return $this;
    }
}