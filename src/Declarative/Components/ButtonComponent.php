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
    
    // 定义组件支持的属性
    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'text', 'disabled'
        ]);
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

    // 重写应用属性方法
    protected function applyAttribute(string $attributeName, $value): void
    {
        if ($attributeName === 'text' && $this->handle) {
            // 如果是 text 属性，更新按钮文本
            Button::setText($this->handle, (string)$value);
        } elseif ($attributeName === 'disabled' && $this->handle) {
            // 如果是 disabled 属性，更新按钮状态
            Button::setEnabled($this->handle, !(bool)$value);
        }
        // 也可以处理其他属性
        parent::applyAttribute($attributeName, $value);
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