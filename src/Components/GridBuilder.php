<?php

namespace Kingbes\Libui\View\Components;

use Exception;
use FFI\CData;
use Kingbes\Libui\Align;
use Kingbes\Libui\Grid;
use Kingbes\Libui\View\ComponentBuilder;

class GridBuilder extends ComponentBuilder
{
    public function getDefaultConfig(): array
    {
        return [
            'padded' => true,
            // spacing defaults changed to provide more visible gaps for forms
            // Note: libui Grid exposes only a boolean padded; we emulate finer spacing via wrapper.
            'columnSpacing' => 8,
            'rowSpacing' => 8,
            'columns' => 12, // total number of logical columns in the grid (for colspan defaults)
        ];
    }

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

    public function place(ComponentBuilder $component, int $row, int $col, int $rowSpan = 1, int $colSpan = 1): static
    {
        // 将布局信息保存到组件的配置中
        // clamp colspan to configured columns
        $maxCols = max(1, (int) $this->getConfig('columns', 12));
        $colSpan = min(max(1, (int) $colSpan), $maxCols);
        $component->setConfig('row', $row);
        $component->setConfig('col', $col);
        $component->setConfig('rowspan', $rowSpan);
        $component->setConfig('colspan', $colSpan);

        // 使用基类的 addChild 方法添加组件
        $this->addChild($component);

        return $this;
    }

    /**
     * 设置网格的逻辑列数（默认为 12）
     */
    public function columns(int $count): static
    {
        $count = max(1, $count);
        return $this->setConfig('columns', $count);
    }

    public function form(array $fields): static
    {
        foreach ($fields as $index => $field) {
            if (!(is_array($field) && isset($field['label'], $field['control']))) {
                continue;
            }

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
        return $this;
    }

    public function padded(bool $padded = true): static
    {
        return $this->setConfig('padded', $padded);
    }

    // 添加组件到网格的核心方法
    protected function createNativeControl(): CData
    {
        return Grid::create();
    }

    // 简化的方法 - 自动计算位置
    protected function applyConfig(): void
    {
        // libui Grid supports only a 'padded' boolean (spacing on/off).
        // If either columnSpacing or rowSpacing is > 0 we enable padded, otherwise disable.
        $colSpacing = (int) $this->getConfig('columnSpacing', 1);
        $rowSpacing = (int) $this->getConfig('rowSpacing', 1);
        $padded = $this->getConfig('padded', true) || ($colSpacing > 0 || $rowSpacing > 0);
        Grid::setPadded($this->handle, $padded);
    }

    // 模板方法 - 快速创建表单布局
    protected function canHaveChildren(): bool
    {
        return true;
    }

    protected function buildChildren(): void
    {
        // 处理通过place()或contains()方法添加的所有children
        if (isset($this->children)) {
            echo '[GridBuilder] Total children to process: ' . count($this->children) . "\n";
            // First, collect metadata for non-container children to allow expansion arbitration
            $collected = [];
            foreach ($this->children as $index => $child) {
                $componentType = get_class($child);
                $componentType = substr($componentType, strrpos($componentType, '\\') + 1);
                echo "[GridBuilder] Building child $index: $componentType\n";
                // If it's a container, process immediately (containers may contain positional children)
                if ($componentType === 'GroupBuilder' || $componentType === 'BoxBuilder') {
                    // Container handling preserved below in original flow
                    // Mark with special flag and handle in the second pass
                    $collected[$index] = ['type' => 'container', 'child' => $child];
                    continue;
                }
                // For normal children, store for later arbitration
                $collected[$index] = ['type' => 'normal', 'child' => $child];
            }

            // Compute desired layout props for normal children
            foreach ($collected as $index => $meta) {
                if ($meta['type'] === 'container') {
                    // will handle containers in the append phase
                    continue;
                }
                $child = $meta['child'];
                $componentType = get_class($child);
                $componentType = substr($componentType, strrpos($componentType, '\\') + 1);

                // Determine position and basic props
                $row = $child->getConfig('row', $index);
                $col = $child->getConfig('col', 0);
                $colspanExplicit = $child->getConfig('colspan', null);
                if ($colspanExplicit === null) {
                    $colspan = in_array($componentType, ['TableBuilder', 'BoxBuilder', 'MultilineEntryBuilder'])
                        ? $this->getConfig('columns', 12)
                        : 1;
                } else {
                    $colspan = $colspanExplicit;
                }
                $rowspan = $child->getConfig('rowspan', 1);

                $expandConfig = $child->getConfig('expand', null);
                $expandExplicit = $expandConfig !== null;
                $hexpand = 0;
                $vexpand = 0;
                if (is_string($expandConfig)) {
                    if ($expandConfig === 'both') {
                        $hexpand = 1;
                        $vexpand = 1;
                    } elseif ($expandConfig === 'horizontal') {
                        $hexpand = 1;
                    } elseif ($expandConfig === 'vertical') {
                        $vexpand = 1;
                    }
                } elseif (is_bool($expandConfig)) {
                    if ($expandConfig) {
                        $hexpand = 1;
                        $vexpand = 1;
                    }
                }
                if ($expandConfig === null) {
                    if ($componentType === 'TableBuilder') {
                        $hexpand = 1;
                        $vexpand = 1;
                    } elseif ($componentType === 'ButtonBuilder') {
                        $hexpand = 1;
                    } elseif ($componentType === 'MultilineEntryBuilder') {
                        $hexpand = 1;
                        $vexpand = 1;
                    } elseif ($componentType === 'CheckboxBuilder' || $componentType === 'RadioBuilder') {
                        $hexpand = 1;
                    }
                }

                // alignment defaults
                $alignConfig = $child->getConfig('align', null);
                $halign = Align::Fill;
                $valign = Align::Fill;
                if ($componentType === 'LabelBuilder') {
                    $halign = Align::Start;
                    $valign = Align::Center;
                } elseif ($componentType === 'EntryBuilder') {
                    $halign = Align::Fill;
                    $valign = Align::Fill;
                } elseif ($componentType === 'ButtonBuilder') {
                    $halign = Align::Fill;
                    $valign = Align::Center;
                } elseif (in_array($componentType, ['TableBuilder', 'BoxBuilder', 'MultilineEntryBuilder'])) {
                    $halign = Align::Fill;
                    $valign = Align::Fill;
                }
                if (is_string($alignConfig)) {
                    $alignMap = [
                        'fill' => Align::Fill,
                        'start' => Align::Start,
                        'center' => Align::Center,
                        'end' => Align::End,
                        'left' => Align::Start,
                        'right' => Align::End,
                    ];
                    if (str_contains($alignConfig, ',')) {
                        [$hStr, $vStr] = array_map('trim', explode(',', $alignConfig, 2));
                        if ($hStr !== '' && isset($alignMap[$hStr]))
                            $halign = $alignMap[$hStr];
                        if ($vStr !== '' && isset($alignMap[$vStr]))
                            $valign = $alignMap[$vStr];
                    } else {
                        $a = trim($alignConfig);
                        if (isset($alignMap[$a])) {
                            $halign = $alignMap[$a];
                            $valign = $alignMap[$a];
                        }
                    }
                }

                $collected[$index] += [
                    'row' => $row,
                    'col' => $col,
                    'colspan' => $colspan,
                    'rowspan' => $rowspan,
                    'hexpand' => $hexpand,
                    'vexpand' => $vexpand,
                    'expandExplicit' => $expandExplicit,
                    'halign' => $halign,
                    'valign' => $valign,
                ];
            }

            // Arbitration: if a TableBuilder is expanding horizontally, suppress implicit horizontal expands in other columns
            $tableHasExpand = false;
            foreach ($collected as $meta) {
                if (($meta['type'] ?? 'normal') !== 'normal') {
                    continue;
                }

                $m = $meta;
                if (isset($m['hexpand']) && $m['hexpand'] == 1) {
                    if ($m['child'] instanceof \Kingbes\Libui\View\Components\TableBuilder) {
                        $tableHasExpand = true;
                        break;
                    }
                }
            }
            if ($tableHasExpand) {
                foreach ($collected as $idx => $meta) {
                    if (($meta['type'] ?? 'normal') !== 'normal') {
                        continue;
                    }

                    if (($collected[$idx]['hexpand'] ?? 0) == 1 && !$collected[$idx]['expandExplicit']) {
                        // suppress implicit expand
                        $collected[$idx]['hexpand'] = 0;
                    }
                }
            }

            // Now perform the actual append: first containers (processed previously), then normal children
            foreach ($collected as $index => $meta) {
                if ($meta['type'] === 'container') {
                    $child = $meta['child'];
                    // existing container processing below will handle building and appending; invoke same logic
                    $componentType = get_class($child);
                    $componentType = substr($componentType, strrpos($componentType, '\\') + 1);
                    echo "[GridBuilder] Processing container: $componentType\n";
                    // re-use original container handling code block
                    $containerChildren = method_exists($child, 'getChildren') ? $child->getChildren() : [];
                    if (!empty($containerChildren)) {
                        echo '[GridBuilder] Processing ' . count($containerChildren) . " children from container\n";
                        foreach ($containerChildren as $containerIndex => $containerChild) {
                            $containerChildType = get_class($containerChild);
                            $containerChildType = substr($containerChildType, strrpos($containerChildType, '\\') + 1);
                            try {
                                $containerChildHandle = $containerChild->build();
                                echo "[GridBuilder] Container child built successfully\n";
                                $row = $containerChild->getConfig('row', $index + $containerIndex);
                                $col = $containerChild->getConfig('col', 0);
                                $containerColspan = $containerChild->getConfig('colspan', null);
                                $colspan = $containerColspan === null
                                    ? $this->getConfig('columns', 12)
                                    : $containerColspan;
                                $rowspan = $containerChild->getConfig('rowspan', 1);
                                $hexpand = 0;
                                $vexpand = 0;
                                if (
                                    $containerChildType === 'CheckboxBuilder'
                                    || $containerChildType === 'RadioBuilder'
                                ) {
                                    $hexpand = 1;
                                } elseif ($containerChildType === 'MultilineEntryBuilder') {
                                    $hexpand = 1;
                                    $vexpand = 1;
                                } elseif ($containerChildType === 'ButtonBuilder') {
                                    $hexpand = 1;
                                }
                                $halign = Align::Fill;
                                $valign = Align::Center;
                                echo
                                    "[GridBuilder] Positioning container child: row=$row, col=$col, colspan=$colspan, rowspan=$rowspan, hexpand=$hexpand, vexpand=$vexpand\n"
                                ;
                                Grid::append(
                                    $this->handle,
                                    $containerChildHandle,
                                    $col,
                                    $row,
                                    $colspan,
                                    $rowspan,
                                    $hexpand,
                                    $halign->value,
                                    $vexpand,
                                    $valign,
                                );
                                echo "[GridBuilder] Container child appended to grid\n";
                            } catch (Exception $e) {
                                echo '[GridBuilder] Error building container child: ' . $e->getMessage() . "\n";
                            }
                        }
                    }
                    continue;
                }

                // Normal child append
                $child = $meta['child'];
                $props = $meta;
                try {
                    $childHandle = $child->build();
                    echo "[GridBuilder] Child built successfully\n";
                    $row = $props['row'];
                    $col = $props['col'];
                    $colspan = $props['colspan'];
                    $rowspan = $props['rowspan'];
                    $hexpand = $props['hexpand'];
                    $vexpand = $props['vexpand'];
                    $halign = $props['halign'];
                    $valign = $props['valign'];
                    echo
                        "[GridBuilder] Positioning: row=$row, col=$col, colspan=$colspan, rowspan=$rowspan, hexpand=$hexpand, vexpand=$vexpand, halign={$halign->name}, valign={$valign->name}\n"
                    ;
                    Grid::append(
                        $this->handle,
                        $childHandle,
                        $col,
                        $row,
                        $colspan,
                        $rowspan,
                        $hexpand,
                        $halign->value,
                        $vexpand,
                        $valign,
                    );
                    echo "[GridBuilder] Child appended to grid\n";
                } catch (Exception $e) {
                    echo '[GridBuilder] Error building child: ' . $e->getMessage() . "\n";
                }
            }

            // end collected processing
        }
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
            if (!$component instanceof ComponentBuilder) {
                continue;
            }

            $existingRow = $component->getConfig('row', null);
            $existingCol = $component->getConfig('col', null);
            if ($existingRow === null && $existingCol === null) {
                // 设置扩展和对齐属性（默认行为）
                $component->setConfig('expand', 'horizontal');
                $component->setConfig('align', 'start,center');
                // 如果配置了间距（rowSpacing 或 columnSpacing），我们用一个垂直 Box 包裹该组件并启用 padded
                $rowSpacing = (int) $this->getConfig('rowSpacing', 1);
                $colSpacing = (int) $this->getConfig('columnSpacing', 1);
                if ($rowSpacing > 0 || $colSpacing > 0) {
                    // 使用垂直 Box 来创建上下间距
                    $wrapper = new BoxBuilder('vertical');
                    $wrapper->setConfig('padded', true);
                    $wrapper->addChild($component);
                    // 将 wrapper 放置到网格，占据整行
                    $this->place($wrapper, $nextRow + $index, 0, 1, $this->getConfig('columns', 12));
                } else {
                    // 将组件放置在网格中（从 nextRow 开始的连续行）
                    $this->place($component, $nextRow + $index, 0, 1, $this->getConfig('columns', 12)); // 占据整个宽度
                }
            } else {
                // 用户已指定位置，直接添加到 children（不要覆盖 row/col 等配置）
                $this->addChild($component);
            }
        }
        return $this;
    }
}
