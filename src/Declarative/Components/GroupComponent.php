<?php

namespace Kingbes\Libui\Declarative\Components;

// 分组容器组件
use FFI\CData;
use Kingbes\Libui\Group;

class GroupComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:group';
    }

    public function render(): CData
    {
        $title = $this->getAttribute('title', '');
        $group = Group::create($title);

        $margined = (bool)$this->getAttribute('margined', true);
        Group::setMargined($group, $margined);

        // 组只能有一个子元素
        if (count($this->children) === 1) {
            $child = $this->children[0]->render();
            Group::setChild($group, $child);
        }

        return $group;
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
