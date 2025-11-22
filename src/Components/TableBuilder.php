<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Table;
use Kingbes\Libui\TableValueType;
use FFI\CData;

class TableBuilder extends ComponentBuilder
{
    private array $data = [];
    private array $columns = [];

    protected function getDefaultConfig(): array
    {
        return [
            'columns' => [],
            'data' => [],
            'selectionMode' => 'single', // single, multiple, none
            'onRowSelected' => null,
            'onCellChanged' => null,
            'editable' => false,
            'sortable' => true,
            'stretchy' => true,
        ];
    }

    protected function createNativeControl(): CData
    {
        $this->columns = $this->getConfig('columns');
        $this->data = $this->getConfig('data');

        // 创建表格模型处理程序
        $modelHandler = Table::modelHandler(
            count($this->columns), // 列数
            TableValueType::String, // 默认列类型
            count($this->data), // 行数
            [$this, 'getCellValue'], // 获取单元格值回调
            $this->getConfig('editable') ? [$this, 'setCellValue'] : null // 设置单元格值回调
        );

        // 创建表格模型
        $model = Table::createModel($modelHandler);

        // 创建表格控件 - 行背景颜色列设为-1（无颜色列）
        $table = Table::create($model, -1);

        return $table;
    }

    protected function applyConfig(): void
    {
        // 添加列
        foreach ($this->columns as $index => $column) {
            $columnConfig = is_string($column) ? ['title' => $column] : $column;
            $this->addTableColumn($index, $columnConfig);
        }

        // 设置选择模式
        $selectionMode = $this->getConfig('selectionMode');
        // Table::setSelectionMode($this->handle, $selectionMode);

        // 绑定事件
        if ($onRowSelected = $this->getConfig('onRowSelected')) {
            // Table::onRowSelected($this->handle, $onRowSelected);
        }
    }

    private function addTableColumn(int $index, array $config): void
    {
        $title = $config['title'] ?? "Column $index";
        $type = $config['type'] ?? 'text';
        $editable = $config['editable'] ?? $this->getConfig('editable');

        switch ($type) {
            case 'text':
                Table::appendTextColumn($this->handle, $title, $index, $editable ? 1 : -1);
                break;
            case 'image':
                Table::appendImageColumn($this->handle, $title, $index);
                break;
            case 'checkbox':
                Table::appendCheckboxColumn($this->handle, $title, $index, $editable ? 1 : -1);
                break;
            case 'progress':
                Table::appendProgressBarColumn($this->handle, $title, $index);
                break;
            case 'button':
                Table::appendButtonColumn($this->handle, $title, $index, $editable ? 1 : -1);
                break;
        }
    }

    // 获取单元格值的回调
    public function getCellValue(CData $model, int $row, int $column): CData
    {
        if (!isset($this->data[$row][$column])) {
            return Table::createValueStr('');
        }

        $value = $this->data[$row][$column];

        if (is_string($value)) {
            return Table::createValueStr($value);
        } elseif (is_int($value)) {
            return Table::createValueInt($value);
        } elseif (is_bool($value)) {
            return Table::createValueInt($value ? 1 : 0);
        } else {
            return Table::createValueStr((string)$value);
        }
    }

    // 设置单元格值的回调
    public function setCellValue(CData $model, int $row, int $column, CData $value): void
    {
        $type = Table::getValueType($value);

        switch ($type) {
            case TableValueType::String:
                $this->data[$row][$column] = Table::valueStr($value);
                break;
            case TableValueType::Int:
                $this->data[$row][$column] = Table::valueInt($value);
                break;
        }

        if ($onCellChanged = $this->getConfig('onCellChanged')) {
            $onCellChanged($row, $column, $this->data[$row][$column]);
        }
    }

    // 链式配置方法
    public function columns(array $columns): static
    {
        return $this->setConfig('columns', $columns);
    }

    public function data(array $data): static
    {
        return $this->setConfig('data', $data);
    }

    public function onRowSelected(callable $callback): static
    {
        return $this->setConfig('onRowSelected', $callback);
    }

    public function editable(bool $editable = true): static
    {
        return $this->setConfig('editable', $editable);
    }
}