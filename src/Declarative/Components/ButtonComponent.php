<?php

namespace Kingbes\Libui\Declarative\Components;

// 按钮组件
use FFI\CData;
use Kingbes\Libui\Button;
use Kingbes\Libui\Declarative\EventBus;

class ButtonComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:button';
    }

    public function render(): CData
    {
        $text = $this->getAttribute('text', '');
        $button = Button::create($text);

        // 修正事件绑定 - 使用 executeEventHandler
        Button::onClicked($button, function() {
            // 使用增强的事件执行
            $result = $this->executeEventHandler('click');

            // 触发全局事件
            EventBus::emit('button:clicked', $this->ref, $result);

            // 调试输出
            error_log("Button clicked: ref={$this->ref}, result=" . print_r($result, true));
        });

        $this->handle = $button;
        return $button;
    }

    public function getValue()
    {
        return $this->handle ? Button::text($this->handle) : '';
    }

    public function setValue($value): void
    {
        if ($this->handle) {
            Button::setText($this->handle, (string)$value);
        }
    }
}