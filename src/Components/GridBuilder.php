<?php

namespace Kingbes\Libui\View\Components;

use FFI\CData;
use Kingbes\Libui\Grid;
use Kingbes\Libui\View\ComponentBuilder;

class GridBuilder extends ComponentBuilder
{
    private array $gridItems = [];

    protected function getDefaultConfig(): array
    {
        return [
            'padded' => true,
            'columnSpacing' => 1,
            'rowSpacing' => 1,
        ];
    }

    protected function createNativeControl(): CData
    {
        return Grid::create();
    }

    protected function applyConfig(): void
    {
        Grid::setPadded($this->handle, $this->getConfig('padded'));
    }

    protected function canHaveChildren(): bool
    {
        return true;
    }

    protected function buildChildren(): void
    {
        foreach ($this->gridItems as $item) {
            $childHandle = $item['component']->build();

            Grid::append(
                $this->handle,
                $childHandle,
                $item['left'],
                $item['top'],
                $item['xspan'],
                $item['yspan'],
                $item['hexpand'] ? 1 : 0,
                $item['halign']->value,
                $item['vexpand'] ? 1 : 0,
                $item['valign']
            );
        }
    }

    // 添加组件到网格的核心方法
    public function place(ComponentBuilder $component, int $row, int $col,
                          int $rowSpan = 1, int $colSpan = 1): GridItemBuilder
    {
        $item = new GridItemBuilder($component, $col, $row, $colSpan, $rowSpan);
        $this->gridItems[] = $item->getConfig();
        return $item;
    }

    // 简化的方法 - 自动计算位置
    public function row(array $components): static
    {
        $currentRow = count($this->gridItems) > 0
            ? max(array_column($this->gridItems, 'top')) + 1
            : 0;

        foreach ($components as $index => $component) {
            $this->place($component, $currentRow, $index);
        }
        return $this;
    }

    // 模板方法 - 快速创建表单布局
    public function form(array $fields): static
    {
        foreach ($fields as $index => $field) {
            if (is_array($field) && isset($field['label'], $field['control'])) {
                $this->place($field['label'], $index, 0)->align('end', 'center');
                $this->place($field['control'], $index, 1)->expand(true, false);
            }
        }
        return $this;
    }

    public function padded(bool $padded = true): static
    {
        return $this->setConfig('padded', $padded);
    }

    /**
     * 在表单下方追加额外的组件（比如按钮行、状态标签等）
     * @param array $components 要追加的组件数组
     * @return static
     */
    public function append(array $components): static
    {
        // 计算下一行的位置
        $nextRow = count($this->gridItems) > 0
            ? max(array_column($this->gridItems, 'top')) + 1
            : 0;

        // 添加每个组件到新行，每个组件占用单独的一行
        foreach ($components as $index => $component) {
            if ($component instanceof ComponentBuilder) {
                // 将组件放置在网格中（从 nextRow 开始的连续行）
                $this->place($component, $nextRow + $index, 0, 1, 2) // 占据两列，即整行
                    ->expand(true, false)  // 水平扩展，填充整行
                    ->align('start', 'center'); // 水平从开始（左对齐），垂直居中
            }
        }

        return $this;
    }
}