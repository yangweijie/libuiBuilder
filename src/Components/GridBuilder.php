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
        // 处理通过place()或contains()方法添加的所有children
        if (isset($this->children)) {
            echo "[GridBuilder] Total children to process: " . count($this->children) . "\n";
            
                        foreach ($this->children as $index => $child) {
            
                            $componentType = get_class($child);
            
                            $componentType = substr($componentType, strrpos($componentType, '\\') + 1);
            
            
            
                            echo "[GridBuilder] Building child $index: $componentType\n";
            
                            
            
                            // 添加组件详情日志
            
                            if (method_exists($child, 'getChildren')) {
            
                                $childChildren = $child->getChildren();
            
                                if (!empty($childChildren)) {
            
                                    echo "[GridBuilder] Child has " . count($childChildren) . " nested children\n";
            
                                    foreach ($childChildren as $nestedIndex => $nestedChild) {
            
                                        $nestedType = get_class($nestedChild);
            
                                        $nestedType = substr($nestedType, strrpos($nestedType, '\\') + 1);
            
                                        echo "[GridBuilder]   Nested child $nestedIndex: $nestedType\n";
            
                                    }
            
                                }
            
                            }
            
            
            
                            // 检查是否是容器组件，如果是则递归处理其子组件
            
                            if ($componentType === 'GroupBuilder' || $componentType === 'BoxBuilder') {
            
                                echo "[GridBuilder] Processing container: $componentType\n";
            
                                
            
                                // 获取容器的子组件
            
                                $containerChildren = method_exists($child, 'getChildren') ? $child->getChildren() : [];
            
                                
            
                                if (!empty($containerChildren)) {
            
                                    echo "[GridBuilder] Processing " . count($containerChildren) . " children from container\n";
            
                                    
            
                                    foreach ($containerChildren as $containerIndex => $containerChild) {
            
                                        $containerChildType = get_class($containerChild);
            
                                        $containerChildType = substr($containerChildType, strrpos($containerChildType, '\\') + 1);
            
                                        
            
                                        echo "[GridBuilder] Building container child $containerIndex: $containerChildType\n";
            
                                        
            
                                        try {
            
                                            $containerChildHandle = $containerChild->build();
            
                                            echo "[GridBuilder] Container child built successfully\n";
            
            
            
                                            // 为容器内的子组件设置默认的 Grid 位置
            
                                            // 如果子组件已经有位置信息则使用，否则使用当前索引
            
                                            $row = $containerChild->getConfig('row', $index + $containerIndex);
            
                                            $col = $containerChild->getConfig('col', 0);
            
                                            $colspan = $containerChild->getConfig('colspan', 12); // 容器内组件通常占满宽度
            
                                            $rowspan = $containerChild->getConfig('rowspan', 1);
            
            
            
                                            // 自动设置扩展属性
            
                                            $hexpand = 0;
            
                                            $vexpand = 0;
            
                                            
            
                                            if ($containerChildType === 'CheckboxBuilder' || $containerChildType === 'RadioBuilder') {
            
                                                $hexpand = 1;
            
                                                echo "[GridBuilder] Auto-setting {$containerChildType} to expand horizontally\n";
            
                                            } elseif ($containerChildType === 'MultilineEntryBuilder') {
            
                                                $hexpand = 1;
            
                                                $vexpand = 1;
            
                                                echo "[GridBuilder] Auto-setting multiline entry to expand both directions\n";
            
                                            } elseif ($containerChildType === 'ButtonBuilder') {
            
                                                $hexpand = 1;
            
                                                echo "[GridBuilder] Auto-setting button to expand horizontally\n";
            
                                            }
            
            
            
                                            // 设置对齐方式
            
                                            $halign = \Kingbes\Libui\Align::Fill;
            
                                            $valign = \Kingbes\Libui\Align::Center;
            
            
            
                                            echo "[GridBuilder] Positioning container child: row=$row, col=$col, colspan=$colspan, rowspan=$rowspan, hexpand=$hexpand, vexpand=$vexpand\n";
            
            
            
                                            \Kingbes\Libui\Grid::append(
            
                                                $this->handle,
            
                                                $containerChildHandle,
            
                                                $col,
            
                                                $row,
            
                                                $colspan,
            
                                                $rowspan,
            
                                                $hexpand,
            
                                                $halign->value,
            
                                                $vexpand,
            
                                                $valign
            
                                            );
            
            
            
                                            echo "[GridBuilder] Container child appended to grid\n";
            
                                        } catch (\Exception $e) {
            
                                            echo "[GridBuilder] Error building container child: " . $e->getMessage() . "\n";
            
                                        }
            
                                    }
            
                                }
            
                                
            
                                // 跳过常规处理，因为我们已经处理了容器内的子组件
            
                                continue;
            
                            }
            
            
            
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
                        } elseif ($componentType === 'MultilineEntryBuilder') {
                            $hexpand = 1;
                            $vexpand = 1;
                            echo "[GridBuilder] Auto-setting multiline entry to expand both directions\n";
                        } elseif ($componentType === 'CheckboxBuilder') {
                            $hexpand = 1;
                            echo "[GridBuilder] Auto-setting checkbox to expand horizontally\n";
                        } elseif ($componentType === 'RadioBuilder') {
                            $hexpand = 1;
                            echo "[GridBuilder] Auto-setting radio to expand horizontally\n";
                        }
                    }

                    // 处理align参数
                    $alignConfig = $child->getConfig('align', null);
                    $halign = Align::Fill;
                    $valign = Align::Fill;

                    // Label、Separator和Button默认垂直居中，避免填充整行
                    // MultilineEntryBuilder也垂直居中，除非它有rowspan > 1
                    if ($componentType === 'LabelBuilder' || $componentType === 'SeparatorBuilder' || $componentType === 'ButtonBuilder') {
                        $valign = Align::Center;
                    } elseif ($componentType === 'MultilineEntryBuilder' && $rowspan <= 1) {
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
    }

    // 添加组件到网格的核心方法
    public function place(ComponentBuilder $component, int $row, int $col,
                          int $rowSpan = 1, int $colSpan = 1): static
    {
        // 将布局信息保存到组件的配置中
        $component->setConfig('row', $row);
        $component->setConfig('col', $col);
        $component->setConfig('rowspan', $rowSpan);
        $component->setConfig('colspan', $colSpan);

        // 使用基类的 addChild 方法添加组件
        $this->addChild($component);

        return $this;
    }

    // 简化的方法 - 自动计算位置
    public function row(array $components): static
    {
        $currentRow = 0;
        
        // 计算当前最大行号
        if (isset($this->children) && count($this->children) > 0) {
            $maxRow = 0;
            foreach ($this->children as $child) {
                $childRow = $child->getConfig('row', 0);
                $childRowspan = $child->getConfig('rowspan', 1);
                $maxRow = max($maxRow, $childRow + $childRowspan - 1);
            }
            $currentRow = $maxRow + 1;
        }

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
                $label = $field['label'];
                $control = $field['control'];
                
                // 设置标签对齐
                $label->setConfig('align', 'end,center');
                
                // 设置控件扩展
                $control->setConfig('expand', 'both');
                
                // 添加到网格
                $this->place($label, $index, 0);
                $this->place($control, $index, 1);
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



                $nextRow = 0;



                



                // 计算当前最大行号



                if (isset($this->children) && count($this->children) > 0) {



                    $maxRow = 0;



                    foreach ($this->children as $child) {



                        $childRow = $child->getConfig('row', 0);



                        $childRowspan = $child->getConfig('rowspan', 1);



                        $maxRow = max($maxRow, $childRow + $childRowspan - 1);



                    }



                    $nextRow = $maxRow + 1;



                }



        



                // 添加每个组件到新行，每个组件占用单独的一行



                foreach ($components as $index => $component) {



                    if ($component instanceof ComponentBuilder) {



                        // 设置扩展和对齐属性



                        $component->setConfig('expand', 'horizontal');



                        $component->setConfig('align', 'start,center');



                        



                        // 将组件放置在网格中（从 nextRow 开始的连续行）



                        $this->place($component, $nextRow + $index, 0, 1, 12); // 占据整个宽度



                    }



                }



                                return $this;



                            }

                }