<?php

namespace Kingbes\Libui\Declarative\Components;

use FFI\CData;
use Kingbes\Libui\Box;

class BoxComponent  extends Component
{
    public function getTagName(): string
    {
        return 'ui:box';
    }

    public function render(): CData
    {
        $direction = $this->getAttribute('direction', 'vertical');

        if ($direction === 'horizontal') {
            $box = Box::newHorizontalBox();
        } else {
            $box = Box::newVerticalBox();
        }

        $padded = (bool)$this->getAttribute('padded', true);
        Box::setPadded($box, $padded);

        // 渲染子组件
        foreach ($this->children as $child) {
            $childHandle = $child->render();
            $stretchy = (bool)$child->getAttribute('stretchy', false);
            Box::append($box, $childHandle, $stretchy);
        }

        $this->handle = $box;
        return $box;
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