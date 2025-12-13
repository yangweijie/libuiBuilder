<?php

namespace Kingbes\Libui\View\Components;

use Exception;
use Kingbes\Libui\Table as LibuiTable;
use Kingbes\Libui\TableValueType;
use Kingbes\Libui\SortIndicator;
use Kingbes\Libui\TableSelectionMode;
use Kingbes\Libui\Align;
use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Grid;
use FFI\CData;

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
        // 只处理通过contains()方法添加的children，避免重复添加
        if (isset($this->children)) {
            foreach ($this->children as $index => $child) {
                $componentType = get_class($child);
                $componentType = substr($componentType, strrpos($componentType, '\\') + 1);
                
                echo "[GridBuilder] Building child $index: $componentType\n";
                
                try {
                    $childHandle = $child->build();
                    echo "[GridBuilder] Child built successfully\n";

                    // 从子组件的config中读取Grid定位信息
                    $row = $child->getConfig('row', $index);
                    $col = $child->getConfig('col', $index);
                    $colspan = $child->getConfig('colspan', 1);
                    $rowspan = $child->getConfig('rowspan', 1);
                    
                    // 处理expand参数（可以是字符串或布尔值）
                    $expandConfig = $child->getConfig('expand', null);
                    $hexpand = 0;
                    $vexpand = 0;
                    
                    if (is_string($expandConfig)) {
                        // 字符串格式: 'both', 'horizontal', 'vertical'
                        if ($expandConfig === 'both') {
                            $hexpand = 1;
                            $vexpand = 1;
                        } elseif ($expandConfig === 'horizontal') {
                            $hexpand = 1;
                        } elseif ($expandConfig === 'vertical') {
                            $vexpand = 1;
                        }
                    } elseif (is_bool($expandConfig)) {
                        // 布尔格式: true表示双向扩展
                        if ($expandConfig) {
                            $hexpand = 1;
                            $vexpand = 1;
                        }
                    }
                    
                    // 如果没有显式设置expand，根据组件类型自动设置
                    if ($expandConfig === null) {
                        if ($componentType === 'TableBuilder') {
                            $hexpand = 1;
                            $vexpand = 1;
                            echo "[GridBuilder] Auto-setting table to expand both directions\n";
                        } elseif ($componentType === 'ButtonBuilder') {
                            $hexpand = 1;
                            echo "[GridBuilder] Auto-setting button to expand horizontally\n";
                        }
                    }
                    
                    // 处理align参数
                    $alignConfig = $child->getConfig('align', null);
                    $halign = Align::Fill;
                    $valign = Align::Fill;
                    
                    // Label、Separator和Button默认垂直居中，避免填充整行
                    if ($componentType === 'LabelBuilder' || $componentType === 'SeparatorBuilder' || $componentType === 'ButtonBuilder') {
                        $valign = Align::Center;
                    }
                    
                    // 如果用户显式设置了align，使用用户设置（覆盖默认值）
                    if (is_string($alignConfig)) {
                        $alignMap = [
                            'fill' => Align::Fill,
                            'start' => Align::Start,
                            'center' => Align::Center,
                            'end' => Align::End,
                            'left' => Align::Fill,  // Label的默认值，不影响 valign
                            'right' => Align::End,
                        ];
                        $halign = $alignMap[$alignConfig] ?? Align::Fill;
                        // 只有在显式设置了布局关键词时才覆盖 valign
                        if (in_array($alignConfig, ['fill', 'start', 'center', 'end'])) {
                            $valign = $alignMap[$alignConfig];
                        }
                    }
                    
                    echo "[GridBuilder] Positioning: row=$row, col=$col, colspan=$colspan, rowspan=$rowspan, hexpand=$hexpand, vexpand=$vexpand, halign={$halign->name}, valign={$valign->name}\n";

                    Grid::append(
                        $this->handle,
                        $childHandle,
                        $col,             // left (col) - 从配置读取
                        $row,             // top (row) - 从配置读取
                        $colspan,         // xspan (colspan) - 从配置读取
                        $rowspan,         // yspan (rowspan) - 从配置读取
                        $hexpand,         // hexpand
                        $halign->value,   // halign (int)
                        $vexpand,         // vexpand
                        $valign           // valign (Align object)
                    );
                    
                    echo "[GridBuilder] Child appended to grid\n";
                } catch (Exception $e) {
                    echo "[GridBuilder] Error building child: " . $e->getMessage() . "\n";
                }
            }
        }
        
// 注意：不再处理gridItems，避免重复添加组件
        // 如果需要使用place()方法，应该将组件也添加到children数组中
    }

    // 添加组件到网格的核心方法
    public function place(ComponentBuilder $component, int $row, int $col,
                          int $rowSpan = 1, int $colSpan = 1): GridItemBuilder
    {
        $item = new GridItemBuilder($component, $col, $row, $colSpan, $rowSpan);
        $this->gridItems[] = $item;  // 存储对象而不是配置数组
        return $item;
    }

    // 简化的方法 - 自动计算位置
    public function row(array $components): static
    {
        $currentRow = count($this->gridItems) > 0
            ? max(array_map(fn($item) => $item->getConfig()['top'], $this->gridItems)) + 1
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

            ? max(array_map(fn($item) => $item->getConfig()['top'], $this->gridItems)) + 1

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