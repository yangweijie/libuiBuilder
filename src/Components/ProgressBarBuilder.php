<?php

namespace Kingbes\Libui\View\Components;

use FFI\CData;
use Kingbes\Libui\ProgressBar;
use Kingbes\Libui\View\Validation\ComponentBuilder;

class ProgressBarBuilder extends ComponentBuilder
{
    public function getDefaultConfig(): array
    {
        return [
            'value' => 0,
            'indeterminate' => false,
        ];
    }

    protected function createNativeControl(): CData
    {
        return ProgressBar::create();
    }

    protected function applyConfig(): void
    {
        $value = $this->getConfig('value');
        if ($this->getConfig('indeterminate')) {
            ProgressBar::setValue($this->handle, -1);
        } else {
            ProgressBar::setValue($this->handle, $value);
        }
    }

    public function getValue(): int
    {
        return $this->getConfig('value', 0);
    }

    public function setValue($value): void
    {
        $this->setConfig('value', (int)$value);
        if ($this->handle) {
            ProgressBar::setValue($this->handle, (int)$value);
        }
    }

    public function value(int $value): static
    {
        return $this->setConfig('value', $value);
    }

    public function indeterminate(bool $indeterminate = true): static
    {
        return $this->setConfig('indeterminate', $indeterminate);
    }
}