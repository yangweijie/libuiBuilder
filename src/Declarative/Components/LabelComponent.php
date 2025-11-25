<?php

namespace Kingbes\Libui\Declarative\Components;

use FFI\CData;
use Kingbes\Libui\Label;

class LabelComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:label';
    }

    public function render(): CData
    {
        $text = $this->getAttribute('text', '');
        return Label::create($text);
    }

    public function getValue()
    {
        return Label::text($this->getHandle());
    }

    public function setValue($value): void
    {
        Label::setText($this->getHandle(), $value);
    }
}