<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Entry;
use FFI\CData;

class EntryBuilder extends ComponentBuilder
{
    protected function getDefaultConfig(): array
    {
        return [
            'text' => '',
            'placeholder' => '',
            'readOnly' => false,
            'onChange' => null,
        ];
    }

    protected function createNativeControl(): CData
    {
        return Entry::create();
    }

    protected function applyConfig(): void
    {
        $text = $this->getConfig('text');
        if ($text) {
            Entry::setText($this->handle, $text);
        }

        $readOnly = $this->getConfig('readOnly');
        if ($readOnly) {
            Entry::setReadOnly($this->handle, true);
        }

        // 绑定change事件
        Entry::onChanged($this->handle, function() {
            $newValue = Entry::text($this->handle);
            $this->setConfig('text', $newValue);

            // 触发change事件
            $this->emit('change', $newValue);

            // 调用用户定义的onChange
            if ($onChange = $this->getConfig('onChange')) {
                $onChange($newValue, $this);
            }
        });
    }

    public function getValue(): string
    {
        return $this->handle ? Entry::text($this->handle) : $this->getConfig('text', '');
    }

    public function setValue($value): void
    {
        $this->setConfig('text', $value);
        if ($this->handle) {
            Entry::setText($this->handle, $value);
        }
    }

    public function text(string $text): static
    {
        return $this->setConfig('text', $text);
    }

    public function placeholder(string $placeholder): static
    {
        return $this->setConfig('placeholder', $placeholder);
    }

    public function onChange(callable $callback): static
    {
        return $this->setConfig('onChange', $callback);
    }
}