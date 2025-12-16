<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Spinbox;
use FFI\CData;

class SpinboxBuilder extends ComponentBuilder
{
    public function getDefaultConfig(): array
    {
        return [
            'min' => 0,
            'max' => 100,
            'value' => 0,
            'onChange' => null,
        ];
    }

    protected function createNativeControl(): CData
    {
        return Spinbox::create(
            $this->getConfig('min'),
            $this->getConfig('max')
        );
    }

    protected function applyConfig(): void
    {
        $value = $this->getConfig('value');
        Spinbox::setValue($this->handle, $value);

        // 绑定change事件
        Spinbox::onChanged($this->handle, function() {
            $newValue = Spinbox::value($this->handle);
            $this->setConfig('value', $newValue);
            $this->emit('change', $newValue);

            if ($onChange = $this->getConfig('onChange')) {
                $onChange($newValue, $this);
            }
        });
    }

    public function getValue(): int
    {
        return $this->handle ? Spinbox::value($this->handle) : $this->getConfig('value', 0);
    }

    public function setValue($value): void
    {
        $this->setConfig('value', (int)$value);
        if ($this->handle) {
            Spinbox::setValue($this->handle, (int)$value);
        }
    }

    public function min(int $min): static
    {
        return $this->setConfig('min', $min);
    }

    public function max(int $max): static
    {
        return $this->setConfig('max', $max);
    }

    public function value(int $value): static
    {
        return $this->setConfig('value', $value);
    }

    public function range(int $min, int $max): static
    {
        return $this->setConfig('min', $min)->setConfig('max', $max);
    }
}