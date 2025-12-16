<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Checkbox;
use FFI\CData;

class CheckboxBuilder extends ComponentBuilder
{
    public function getDefaultConfig(): array
    {
        return [
            'text' => '',
            'checked' => false,
            'disabled' => false,
            'tristate' => false, // 三态支持
            'onToggle' => null,
            'onChange' => null,
        ];
    }

    protected function createNativeControl(): CData
    {
        return Checkbox::create($this->getConfig('text'));
    }

    protected function applyConfig(): void
    {
        $checked = $this->getConfig('checked');
        Checkbox::setChecked($this->handle, $checked);

        $disabled = $this->getConfig('disabled');
        if ($disabled) {
            // Control::disable($this->handle);
        }

        // 绑定toggle事件
        Checkbox::onToggled($this->handle, function() {
            $newChecked = Checkbox::checked($this->handle);
            $oldChecked = $this->getConfig('checked');

            $this->setConfig('checked', $newChecked);
            $this->emit('change', $newChecked, $oldChecked);

            // 用户回调
            if ($onToggle = $this->getConfig('onToggle')) {
                $onToggle($newChecked, $this);
            }

            if ($onChange = $this->getConfig('onChange')) {
                $onChange($newChecked, $this);
            }
        });
    }

    public function getValue(): bool
    {
        return $this->handle ? Checkbox::checked($this->handle) : $this->getConfig('checked', false);
    }

    public function setValue($value): void
    {
        $checked = (bool)$value;
        $this->setConfig('checked', $checked);
        if ($this->handle) {
            Checkbox::setChecked($this->handle, $checked);
        }
    }

    // 链式配置方法
    public function text(string $text): static
    {
        return $this->setConfig('text', $text);
    }

    public function checked(bool $checked = true): static
    {
        return $this->setConfig('checked', $checked);
    }

    public function disabled(bool $disabled = true): static
    {
        return $this->setConfig('disabled', $disabled);
    }

    public function onToggle(callable $callback): static
    {
        return $this->setConfig('onToggle', $callback);
    }

    public function toggle(): static
    {
        $this->setValue(!$this->getValue());
        return $this;
    }
}