<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Button;
use FFI\CData;

class ButtonBuilder extends ComponentBuilder
{
    public function getDefaultConfig(): array
    {
        return [
            'text' => 'Button',
            'onClick' => null,
            'stretchy' => false,
        ];
    }

    protected function createNativeControl(): CData
    {
        return Button::create($this->getConfig('text'));
    }

    protected function applyConfig(): void
    {
        $onClick = $this->getConfig('onClick');
        if ($onClick) {
            Button::onClicked($this->handle, function() use ($onClick) {
                // 触发click事件
                $this->emit('click');

                // 调用用户处理器，传入组件引用和状态管理器
                $onClick($this, $this->state());
            });
        }
    }

    public function text(string $text): static
    {
        return $this->setConfig('text', $text);
    }

    public function onClick(callable $callback): static
    {
        return $this->setConfig('onClick', $callback);
    }

    /**
     * 设置组件值 - 子类实现
     */
    public function setValue($value): void
    {
        $this->setConfig('value', $value);
        if($this->handle){
            Button::setText($this->handle, $value);
        }
    }
}