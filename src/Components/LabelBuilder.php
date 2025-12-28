<?php

namespace Kingbes\Libui\View\Components;

use FFI\CData;
use Kingbes\Libui\Label;
use Kingbes\Libui\View\Validation\ComponentBuilder;

class LabelBuilder extends ComponentBuilder
{
    public function getDefaultConfig(): array
    {
        return [
            'text' => '',
            'align' => 'left', // left, center, right
            'color' => null,
            'font' => null,
        ];
    }

    protected function createNativeControl(): CData
    {
        return Label::create($this->getConfig('text'));
    }

    protected function applyConfig(): void
    {
        $text = $this->getConfig('text');
        if ($text) {
            Label::setText($this->handle, $text);
        }
    }

    public function getValue(): string
    {
        return $this->handle ? Label::text($this->handle) : $this->getConfig('text', '');
    }

    public function setValue($value): void
    {
        $this->setConfig('text', $value);
        if ($this->handle) {
            Label::setText($this->handle, $value);
        }
    }

    // 链式配置方法
    public function text(string $text): static
    {
        return $this->setConfig('text', $text);
    }

    public function align(string $align): static
    {
        return $this->setConfig('align', $align);
    }

    public function color(array $color): static
    {
        return $this->setConfig('color', $color);
    }
}