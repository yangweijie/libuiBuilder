<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Grid;
use Kingbes\Libui\Align;

/**
 * 网格布局构建器
 */
class GridBuilder extends ComponentBuilder
{
    /** @var array 网格项列表 */
    protected array $items = [];

    /**
     * 设置列数
     *
     * @param int $columns 列数
     * @return $this
     */
    public function columns(int $columns): self
    {
        $this->config['columns'] = $columns;
        return $this;
    }

    /**
     * 设置内边距
     *
     * @param bool $padded 是否有内边距
     * @return $this
     */
    public function padded(bool $padded): self
    {
        $this->config['padded'] = $padded;
        return $this;
    }

    /**
     * 追加组件到网格
     *
     * @param ComponentBuilder $component 组件
     * @param int $row 行索引
     * @param int $col 列索引
     * @param int $rowspan 行跨度
     * @param int $colspan 列跨度
     * @param bool $hexpand 水平展开
     * @param string $halign 水平对齐 (fill, start, center, end)
     * @param bool $vexpand 垂直展开
     * @param string $valign 垂直对齐 (fill, start, center, end)
     * @return $this
     */
    public function append(
        ComponentBuilder $component,
        int $row,
        int $col,
        int $rowspan = 1,
        int $colspan = 1,
        bool $hexpand = false,
        string $halign = 'fill',
        bool $vexpand = false,
        string $valign = 'fill'
    ): self {
        $this->items[] = [
            'component' => $component,
            'row' => $row,
            'col' => $col,
            'rowspan' => $rowspan,
            'colspan' => $colspan,
            'hexpand' => $hexpand,
            'halign' => $halign,
            'vexpand' => $vexpand,
            'valign' => $valign,
        ];
        return $this;
    }

    /**
     * 添加组件到指定位置（兼容旧版本）
     *
     * @param ComponentBuilder $component 组件
     * @param int $row 行索引
     * @param int $col 列索引
     * @param int $rowspan 行跨度
     * @param int $colspan 列跨度
     * @return $this
     */
    public function place(
        ComponentBuilder $component,
        int $row,
        int $col,
        int $rowspan = 1,
        int $colspan = 1
    ): self {
        return $this->append($component, $row, $col, $rowspan, $colspan, false, 'fill', false, 'fill');
    }

    /**
     * 快速创建表单布局
     *
     * @param array $fields 字段数组 ['label' => LabelBuilder, 'control' => ComponentBuilder]
     * @param int $startRow 起始行
     * @return $this
     */
    public function form(array $fields, int $startRow = 0): self
    {
        foreach ($fields as $index => $field) {
            $row = $startRow + $index;
            
            if (isset($field['label'])) {
                $this->append($field['label'], $row, 0, 1, 1, false, 'end', false, 'center');
            }
            
            if (isset($field['control'])) {
                $col = isset($field['label']) ? 1 : 0;
                $colspan = isset($field['label']) ? 1 : 2;
                $this->append($field['control'], $row, $col, 1, $colspan, true, 'fill', false, 'center');
            }
        }
        return $this;
    }

    /**
     * 构建网格组件
     *
     * @return CData 网格句柄
     */
    protected function buildComponent(): CData
    {
        // 创建网格
        $this->handle = Grid::create();

        // 设置内边距
        if (isset($this->config['padded'])) {
            Grid::setPadded($this->handle, $this->config['padded']);
        }

        // 添加网格项
        foreach ($this->items as $item) {
            echo "[GRID_DEBUG] 构建子组件: " . get_class($item['component']) . "\n";
            $componentHandle = $item['component']->build();
            echo "[GRID_DEBUG] 子组件构建完成\n";
            
            $left = $item['col'];
            $top = $item['row'];
            $xspan = $item['colspan'];
            $yspan = $item['rowspan'];
            $hexpand = $item['hexpand'] ? 1 : 0;
            $vexpand = $item['vexpand'] ? 1 : 0;
            
            $halign = $this->mapAlign($item['halign']);
            $valign = $this->mapAlign($item['valign']);

            Grid::append(
                $this->handle,
                $componentHandle,
                $left,
                $top,
                $xspan,
                $yspan,
                $hexpand,
                $halign->value,
                $vexpand,
                $valign
            );
        }

        return $this->handle;
    }

    /**
     * 映射对齐方式
     *
     * @param string $align 对齐字符串
     * @return Align
     */
    protected function mapAlign(string $align): Align
    {
        return match($align) {
            'start' => Align::Start,
            'center' => Align::Center,
            'end' => Align::End,
            default => Align::Fill,
        };
    }

    /**
     * 获取组件类型
     *
     * @return string
     */
    public function getType(): string
    {
        return 'grid';
    }

    /**
     * 获取网格项
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * 获取组件值（实现ComponentInterface）
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->config['value'] ?? null;
    }

    /**
     * 设置组件值（实现ComponentInterface）
     *
     * @param mixed $value
     * @return self
     */
    public function setValue(mixed $value): self
    {
        $this->config['value'] = $value;
        return $this;
    }

    
}
