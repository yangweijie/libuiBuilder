<?php

namespace Kingbes\Libui\View;

use Kingbes\Libui\View\Components\GridBuilder;

class ResponsiveGridBuilder
{
    private int $totalColumns;
    private int $currentRow = 0;
    private int $currentCol = 0;
    private array $items = [];

    public function __construct(int $columns = 12)
    {
        $this->totalColumns = $columns;
    }

    public function col(ComponentBuilder $component, int $span = 1): static
    {
        // 如果当前行空间不足，换行
        if ($this->currentCol + $span > $this->totalColumns) {
            $this->currentRow++;
            $this->currentCol = 0;
        }

        $this->items[] = [
            'component' => $component,
            'row' => $this->currentRow,
            'col' => $this->currentCol,
            'span' => $span
        ];

        $this->currentCol += $span;
        return $this;
    }

    public function newRow(): static
    {
        $this->currentRow++;
        $this->currentCol = 0;
        return $this;
    }

    public function build(): GridBuilder
    {
        $grid = Builder::grid();

        foreach ($this->items as $item) {
            $gridItem = $grid->place(
                $item['component'],
                $item['row'],
                $item['col'],
                1,
                $item['span']
            );
            
            // 为按钮等控件设置合适的对齐方式，避免压缩
            $componentClass = get_class($item['component']);
            if (strpos($componentClass, 'Button') !== false) {
                // 按钮使用居中对齐，不拉伸填满整个网格单元格
                $gridItem->align('center', 'center')->expand(false, false);
            } else {
                // 其他控件如标签可以拉伸
                $gridItem->align('fill', 'center')->expand(true, false);
            }
        }

        return $grid;
    }
}

// 使用响应式网格
//$responsiveLayout = ResponsiveGrid::create(12)
//    ->col(Builder::label()->text('标题'), 12)  // 全宽
//    ->col(Builder::label()->text('左侧'), 6)   // 半宽
//    ->col(Builder::label()->text('右侧'), 6)   // 半宽
//    ->col(Builder::button()->text('1/4'), 3)  // 四分之一宽
//    ->col(Builder::button()->text('1/4'), 3)
//    ->col(Builder::button()->text('1/4'), 3)
//    ->col(Builder::button()->text('1/4'), 3)
//    ->build();