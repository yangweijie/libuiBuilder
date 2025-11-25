<?php

namespace Kingbes\Libui\Declarative\Components;

use FFI\CData;
use Kingbes\Libui\Separator;

class SeparatorComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:separator';
    }

    public function render(): CData
    {
        $direction = $this->getAttribute('direction', 'horizontal');

        return $direction === 'vertical' ?
            Separator::createVertical() :
            Separator::createHorizontal();
    }

    public function getValue()
    {
        return null;
    }

    public function setValue($value): void
    {
        // TODO: Implement setValue() method.
    }
}