<?php

namespace Kingbes\Libui\Declarative\Components;

use FFI\CData;
use Kingbes\Libui\Tab;

class TabComponent extends Component{
    public function getTagName(): string
    {
        return 'ui:tab';
    }

    public function render(): CData
    {
        $tab = Tab::create();

        // 渲染标签页（每个子组件需要title属性）
        foreach ($this->children as $child) {
            $childHandle = $child->render();
            $title = $child->getAttribute('title', 'Tab');
            Tab::append($tab, $title, $childHandle);
        }

        return $tab;
    }

    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'margined'
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