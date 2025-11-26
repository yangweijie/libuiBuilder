<?php

namespace Kingbes\Libui\Declarative\Components;

use FFI\CData;
use Kingbes\Libui\Label;
use Kingbes\Libui\Declarative\StateManager;

class LabelComponent extends Component
{
    private $originalTextExpression;

    public function getTagName(): string
    {
        return 'ui:label';
    }
    
    // 定义组件支持的属性
    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'text'
        ]);
    }

    public function render(): CData
    {
        $text = $this->getAttribute('text', '');
        $this->originalTextExpression = $text; // 保存原始表达式

        $label = Label::create($text);

        $this->handle = $label;
        return $label;
    }

    // 重写应用属性方法
    protected function applyAttribute(string $attributeName, $value): void
    {
        if ($attributeName === 'text' && $this->handle) {
            // 如果是 text 属性，更新标签文本
            Label::setText($this->handle, (string)$value);
        }
        // 也可以处理其他属性
        parent::applyAttribute($attributeName, $value);
    }

    public function getValue()
    {
        return Label::text($this->getHandle());
    }

    public function setValue($value): void
    {
        Label::setText($this->getHandle(), $value);
        // 更新属性
        $this->setAttribute('text', $value);
    }
}