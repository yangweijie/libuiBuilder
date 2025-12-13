<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Radio;
use FFI\CData;

class RadioBuilder extends ComponentBuilder
{
    protected function getDefaultConfig(): array
    {
        return [
            'items' => [],
            'selected' => -1,
            'onSelected' => null,
        ];
    }

    protected function createNativeControl(): CData
    {
        return Radio::create();
    }

    protected function applyConfig(): void
    {
        // 添加选项
        $items = $this->getConfig('items');
        foreach ($items as $item) {
            Radio::append($this->handle, is_array($item) ? $item['text'] : $item);
        }

        // 设置默认选中
        $selected = $this->getConfig('selected');
        if ($selected >= 0) {
            Radio::setSelected($this->handle, $selected);
        }

        // 绑定选择事件
        Radio::onSelected($this->handle, function() {
            $newSelected = Radio::selected($this->handle);
            $this->setConfig('selected', $newSelected);
            $this->emit('change', $newSelected);

            if ($onSelected = $this->getConfig('onSelected')) {
                $items = $this->getConfig('items', []);
                $item = $items[$newSelected] ?? null;
                $itemText = is_array($item) ? $item['text'] : $item;
                $onSelected($newSelected, $itemText, $this);
            }
        });
    }

    public function getValue(): int
    {
        return $this->handle ? Radio::selected($this->handle) : $this->getConfig('selected', -1);
    }

    public function setValue($value): void
    {
        $this->setConfig('selected', (int)$value);
        if ($this->handle) {
            Radio::setSelected($this->handle, (int)$value);
        }
    }

    public function items(array $items): static
    {
        $this->setConfig('items', $items);
        return $this;
    }

    public function addItem(string $text, $value = null): static
    {
        $items = $this->getConfig('items');
        $items[] = $value !== null ? ['text' => $text, 'value' => $value] : $text;
        return $this->setConfig('items', $items);
    }

    public function selected(int $selected): static
    {
        return $this->setConfig('selected', $selected);
    }
}