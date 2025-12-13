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

        // 调试信息
        $componentType = get_class($component);
        $componentType = substr($componentType, strrpos($componentType, '\\') + 1);
        echo "[ResponsiveGrid] 添加组件: {$componentType} -> 行:{$this->currentRow}, 列:{$this->currentCol}, 跨度:{$span}\n";

        $this->currentCol += $span;
        return $this;
    }

    public function newRow(): static
    {
        $this->currentRow++;
        $this->currentCol = 0;
        return $this;
    }

    public function newRows($rows){
        for ($i = 0; $i < $rows; $i++){
            $this->newRow();
        }
        return $this;
    }

    public function build(): GridBuilder

    {

        $grid = Builder::grid();
        
        echo "[ResponsiveGrid] 开始构建网格，总共 " . count($this->items) . " 个组件\n";


        foreach ($this->items as $index => $item) {

            $gridItem = $grid->place(

                $item['component'],

                $item['row'],

                $item['col'],

                1,  // rowspan 固定为 1

                $item['span']  // colspan

            );

            

            // 为不同类型的组件设置合适的对齐方式
            $componentClass = get_class($item['component']);
            $componentType = substr($componentClass, strrpos($componentClass, '\\') + 1);
            
            if (strpos($componentClass, 'Button') !== false) {
                // 按钮使用居中对齐，不拉伸填满整个网格单元格
                $gridItem->align('center', 'center')->expand(false, false);
                echo "[ResponsiveGrid] 设置按钮对齐: 居中居中，不扩展\n";
            } elseif (strpos($componentClass, 'Table') !== false) {
                // 表格需要填充整个空间
                $gridItem->align('fill', 'fill')->expand(true, true);
                echo "[ResponsiveGrid] 设置表格对齐: 填充填充，双向扩展\n";
            } elseif (strpos($componentClass, 'Separator') !== false) {
                // 分隔线需要水平填充
                $gridItem->align('fill', 'center')->expand(true, false);
                echo "[ResponsiveGrid] 设置分隔线对齐: 填充居中，水平扩展\n";
            } else {
                // 其他控件如标签可以拉伸
                $gridItem->align('fill', 'center')->expand(true, false);
                echo "[ResponsiveGrid] 设置标签对齐: 填充居中，水平扩展\n";
            }

        }



        echo "[ResponsiveGrid] 网格构建完成\n";
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