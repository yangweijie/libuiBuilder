<?php

namespace Kingbes\Libui\Declarative\Components;

use FFI\CData;
use Kingbes\Libui\Form;

// 表单布局组件

class FormComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:form';
    }

    public function render(): CData
    {
        $form = Form::create();

        $padded = (bool)$this->getAttribute('padded', true);
        Form::setPadded($form, $padded);

        // 渲染子组件（每个子组件需要有label属性）
        foreach ($this->children as $child) {
            $childHandle = $child->render();
            $label = $child->getAttribute('label', '');
            $stretchy = (bool)$child->getAttribute('stretchy', false);
            Form::append($form, $label, $childHandle, $stretchy);
        }

        return $form;
    }

    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'padded',
        ]);
    }

    public function getValue()
    {
        // TODO: Implement getValue() method.
    }

    public function setValue($value): void
    {
        // TODO: Implement setValue() method.
    }
}