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
     * 构建网格
     *
     * @return CData 网格句柄
     */
    public function build(): CData
    {
        // 创建网格
        $this->handle = Grid::create();

        // 设置内边距
        if (isset($this->config['padded'])) {
            Grid::setPadded($this->handle, $this->config['padded']);
        }

        // 添加网格项
        foreach ($this->items as $item) {
            $componentHandle = $item['component']->build();
            
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

        // 注册到状态管理器
        if ($this->id && $this->stateManager) {
            $this->stateManager->registerComponent($this->id, $this);
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
}
