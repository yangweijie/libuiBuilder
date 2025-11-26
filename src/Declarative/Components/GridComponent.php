<?php

namespace Kingbes\Libui\Declarative\Components;

use FFI\CData;
use Kingbes\Libui\Grid;

class GridComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:grid';
    }

    public function render(): CData
    {
        $grid = Grid::create();

        $padded = (bool)$this->getAttribute('padded', true);
        Grid::setPadded($grid, $padded);

        // 渲染子组件（每个子组件需要位置属性）
        foreach ($this->children as $child) {
            $childHandle = $child->render();

            // 网格位置参数
            $left = (int)$child->getAttribute('left', 0);
            $top = (int)$child->getAttribute('top', 0);
            $xspan = (int)$child->getAttribute('xspan', 1);
            $yspan = (int)$child->getAttribute('yspan', 1);
            $hexpand = (bool)$child->getAttribute('hexpand', false);
            $halign = $this->parseAlign($child->getAttribute('halign', 'fill'));
            $vexpand = (bool)$child->getAttribute('vexpand', false);
            $valign = $this->parseAlign($child->getAttribute('valign', 'fill'));

            Grid::append(
                $grid, $childHandle, $left, $top, $xspan, $yspan,
                $hexpand ? 1 : 0, $halign, $vexpand ? 1 : 0, $valign
            );
        }

        return $grid;
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