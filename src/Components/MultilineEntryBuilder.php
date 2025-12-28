<?php

namespace Kingbes\Libui\View\Components;

use FFI\CData;
use Kingbes\Libui\MultilineEntry;
use Kingbes\Libui\View\Validation\ComponentBuilder;

class MultilineEntryBuilder extends ComponentBuilder
{
    public function getDefaultConfig(): array
    {
        return [
            'text' => '',
            'readOnly' => false,
            'wordWrap' => true,
            'maxLength' => null,
            'placeholder' => '',
            'onChange' => null,
        ];
    }

    protected function createNativeControl(): CData
    {
        return $this->getConfig('wordWrap')
            ? MultilineEntry::create()
            : MultilineEntry::createNonWrapping();
    }

    protected function applyConfig(): void
    {
        $text = $this->getConfig('text');
        if ($text) {
            MultilineEntry::setText($this->handle, $text);
        }

        $readOnly = $this->getConfig('readOnly');
        if ($readOnly) {
            MultilineEntry::setReadOnly($this->handle, true);
        }

        // 绑定change事件
        MultilineEntry::onChanged($this->handle, function() {
            $newValue = MultilineEntry::text($this->handle);
            $this->setConfig('text', $newValue);
            $this->emit('change', $newValue);

            if ($onChange = $this->getConfig('onChange')) {
                $onChange($newValue, $this);
            }
        });
    }

    public function getValue(): string
    {
        return $this->handle ? MultilineEntry::text($this->handle) : $this->getConfig('text', '');
    }

    public function setValue($value): void
    {
        $this->setConfig('text', (string)$value);
        if ($this->handle) {
            MultilineEntry::setText($this->handle, (string)$value);
        }
    }

    public function text(string $text): static
    {
        return $this->setConfig('text', $text);
    }

    public function readOnly(bool $readOnly = true): static
    {
        return $this->setConfig('readOnly', $readOnly);
    }

    public function wordWrap(bool $wordWrap = true): static
    {
        return $this->setConfig('wordWrap', $wordWrap);
    }

    public function placeholder(string $placeholder): static
    {
        return $this->setConfig('placeholder', $placeholder);
    }
}