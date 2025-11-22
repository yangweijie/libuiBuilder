<?php

namespace Kingbes\Libui\View\Components;

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Box;
use FFI\CData;

class BoxBuilder extends ComponentBuilder
{
    private string $direction;

    public function __construct(string $direction, array $config = [])
    {
        $this->direction = $direction;
        parent::__construct($config);
    }

    protected function getDefaultConfig(): array
    {
        return [
            'padded' => true,
            'stretchy' => false, // 子元素是否可拉伸
        ];
    }

    protected function createNativeControl(): CData
    {
        return $this->direction === 'vertical'
            ? Box::newVerticalBox()
            : Box::newHorizontalBox();
    }

    protected function applyConfig(): void
    {
        Box::setPadded($this->handle, $this->getConfig('padded'));
    }

    protected function canHaveChildren(): bool
    {
        return true;
    }

    protected function buildChildren(): void
    {
        foreach ($this->children as $child) {
            $childHandle = $child->build();
            $stretchy = $child->getConfig('stretchy', $this->getConfig('stretchy'));
            Box::append($this->handle, $childHandle, $stretchy);
        }
    }

    public function padded(bool $padded = true): static
    {
        return $this->setConfig('padded', $padded);
    }

    public function stretchy(bool $stretchy = true): static
    {
        return $this->setConfig('stretchy', $stretchy);
    }
}