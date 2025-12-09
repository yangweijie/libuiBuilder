<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Table as LibuiTable;
use Kingbes\Libui\TableValueType;
use Kingbes\Libui\SortIndicator;
use Kingbes\Libui\TableSelectionMode;
use FFI\CData;

class TableBuilder extends ComponentBuilder
{
    public $tableModel = null;
    public $tableHandler = null;
    public array $originalData = [];  // 保存原始数据用于排序
    public array $displayData = [];   // 当前显示的数据
    public ?int $sortColumn = null;   // 当前排序列
    public ?string $sortDirection = null; // 当前排序方向 ('asc' 或 'desc')

    public function getSortEum(): SortIndicator
    {
        return $this->sortDirection == 'asc'? SortIndicator::Ascending:SortIndicator::Descending;
    }

    protected function getDefaultConfig(): array
    {
        return [
            'headers' => [],
            'data' => [],
            'options' => [
                'sortable' => false,
                'multiSelect' => false,
                'headerVisible' => true,
                'columnWidths' => []
            ],
            'eventHandlers' => []
        ];
    }

    protected function createNativeControl(): CData
    {
        $headers = $this->getConfig('headers', []);
        $data = $this->getConfig('data', []);
        $options = array_merge($this->getDefaultConfig()['options'], $this->getConfig('options', []));

        // 保存原始数据
        $this->originalData = $data;

        // 如果需要排序，对数据进行排序
        $this->displayData = $data;
        if ($options['sortable'] && $this->sortColumn !== null) {
            $this->displayData = $this->sortDataByColumnAndDirection($this->originalData, $headers, $this->sortColumn, $this->sortDirection);
        }

        $numColumns = count($headers);
        $numRows = count($this->displayData);
        
        // 创建表格模型处理器 - 使用实际数据的行数，避免空白行和滚动条
        $maxRows = max($numRows, 1); // 至少1行，避免空表格
        
        $this->tableHandler = LibuiTable::modelHandler(
            $numColumns,
            TableValueType::String, // 默认使用字符串类型
            $maxRows,
            function($handler, $row, $column) use ($headers) {
                // 从当前配置获取最新数据，而不是使用创建时的副本
                $currentData = $this->getConfig('data', []);
                $options = $this->getConfig('options', []);
                
                // 如果当前有排序，则应用排序
                if ($this->sortColumn !== null && $options['sortable']) {
                    $currentData = $this->sortDataByColumnAndDirection(
                        $currentData, 
                        $headers, 
                        $this->sortColumn, 
                        $this->sortDirection
                    );
                }
                
                if (isset($currentData[$row])) {
                    $rowData = $currentData[$row];
                    // 使用列索引直接访问数据，因为数据是索引数组
                    if (isset($rowData[$column])) {
                        $value = $rowData[$column];
                        // 确保值是字符串
                        return LibuiTable::createValueStr((string) $value);
                    }
                }
                return LibuiTable::createValueStr('');
            }
        );

        // 创建表格模型
        $this->tableModel = LibuiTable::createModel($this->tableHandler);
        
        // 创建表格控件
        $tableControl = LibuiTable::create($this->tableModel, -1); // -1 表示不使用行背景颜色列
        
        // 添加列到表格控件（不是模型）
        foreach ($headers as $index => $header) {
            LibuiTable::appendTextColumn($tableControl, $header, $index, false);
        }

        return $tableControl;
    }

    protected function applyConfig(): void
    {
        if (!$this->handle) {
            return;
        }
        
        $options = array_merge($this->getDefaultConfig()['options'], $this->getConfig('options', []));
        $eventHandlers = $this->getConfig('eventHandlers', []);
        
        // 设置表头可见性
        // 注意：uiTableSetHeaderVisible 函数在当前 libui 版本中可能不可用
        // 暂时注释掉，等待 libui 更新或找到替代方案
         if (isset($options['headerVisible'])) {
             LibuiTable::setHeaderVisible($this->handle, $options['headerVisible']);
         }
        
        // 设置选择模式
        $selectionMode = $options['multiSelect'] ? 
            TableSelectionMode::ZeroOrMany : TableSelectionMode::One;
        LibuiTable::setSelectionMode($this->handle, $selectionMode);
        
        // 设置列宽度
        foreach ($options['columnWidths'] as $columnIndex => $width) {
            LibuiTable::setColumnWidth($this->handle, $columnIndex, $width);
        }
        
        // 添加事件监听
        foreach ($eventHandlers as $event => $handler) {
            if ($event === 'onRowClicked') {
                LibuiTable::onRowClicked($this->handle, function($table, $row) use ($handler) {
                    $handler($this, $row); // 传递组件实例而不是原生控件
                });
            } elseif ($event === 'onRowDoubleClicked') {
                LibuiTable::onRowDoubleClicked($this->handle, function($table, $row) use ($handler) {
                    $handler($this, $row);
                });
            } elseif ($event === 'onSelectionChanged') {
                LibuiTable::onSelectionChanged($this->handle, function($table) use ($handler) {
                    $handler($this);
                });
            } elseif ($event === 'onHeaderClicked') {
                LibuiTable::onHeaderClicked($this->handle, function($table, $column) use ($handler, $options) {
                    $this->handleHeaderClick($column, $options['sortable']);
                    $handler($this, $column, $this->sortColumn, $this->sortDirection);
                });
            }
        }
    }

    /**
     * 设置表头
     */
    public function headers(array $headers): self
    {
        return $this->setConfig('headers', $headers);
    }

    /**
     * 设置表格数据
     */
    public function data(array $data): self
    {
        // 保存原始数据并更新显示数据
        $this->originalData = $data;
        
        // 如果当前有排序，则应用排序
        $headers = $this->getConfig('headers', []);
        if ($this->sortColumn !== null && $this->getConfig('options', [])['sortable']) {
            $this->displayData = $this->sortDataByColumnAndDirection($this->originalData, $headers, $this->sortColumn, $this->sortDirection);
        } else {
            $this->displayData = $data;
        }
        
        return $this->setConfig('data', $data);
    }

    /**
     * 设置表格选项
     */
    public function options(array $options): self
    {
        $currentOptions = $this->getConfig('options', []);
        $mergedOptions = array_merge($currentOptions, $options);
        return $this->setConfig('options', $mergedOptions);
    }

    /**
     * 添加事件处理器
     */
    public function onEvent(string $event, callable $handler): self
    {
        $eventHandlers = $this->getConfig('eventHandlers', []);
        $eventHandlers[$event] = $handler;
        return $this->setConfig('eventHandlers', $eventHandlers);
    }

    /**
     * 更新表格数据
     */
    public function updateData(array $data): void
    {
        $this->setConfig('data', $data);
        
        // 注意：ui库的模型不能简单地更新，需要重新创建模型
        // 这是一个高级功能，可能需要在实际应用中实现模型更新方法
    }

    /**
     * 获取表格选择
     */
    public function getSelection()
    {
        if ($this->handle) {
            return LibuiTable::getSelection($this->handle);
        }
        return null;
    }

    /**
     * 设置表格选择
     */
    public function setSelection($selection): void
    {
        if ($this->handle) {
            LibuiTable::setSelection($this->handle, $selection);
        }
    }

    /**
     * 获取表格选择模式
     */
    public function getSelectionMode(): TableSelectionMode
    {
        if ($this->handle) {
            return LibuiTable::selectionMode($this->handle);
        }
        return TableSelectionMode::None;
    }

    function clearAllSortIndicators($table, $numColumns = 5): void
    {
        for ($i = 0; $i < $numColumns; $i++) {
            LibuiTable::setHeaderSortIndicator($table, $i, SortIndicator::None);
        }
    }

    /**
     * 处理表头点击事件（用于排序）
     */
    private function handleHeaderClick(int $column, bool $sortable): void
    {
        if (!$sortable) {
            return;
        }

        $headers = $this->getConfig('headers', []);
        if (!isset($headers[$column])) {
            return;
        }

        $this->clearAllSortIndicators($this->handle, count($headers));

        // 如果点击了同一列，则切换排序方向，否则按升序开始
        if ($this->sortColumn === $column) {
            if ($this->sortDirection === 'asc') {
                $this->sortDirection = 'desc';
            } else {
                $this->sortDirection = 'asc';
            }
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }

        // 设置排序指示器
        LibuiTable::setHeaderSortIndicator($this->handle, $column, $this->getSortEum());
        
        // 自动排序并刷新表格
        $this->sortData();
        $this->refreshTable();
    }

    /**
     * 对数据进行排序（根据当前设置的排序列和方向）
     */
    public function sortData(): void
    {
        if ($this->sortColumn === null || $this->sortDirection === null) {
            $this->displayData = $this->originalData;
            return;
        }

        $headers = $this->getConfig('headers', []);
        if (!isset($headers[$this->sortColumn])) {
            return;
        }

        $this->displayData = $this->sortDataByColumnAndDirection($this->originalData, $headers, $this->sortColumn, $this->sortDirection);
    }
    
    /**
     * 按指定列和方向对数据进行排序
     */
    private function sortDataByColumnAndDirection(array $data, array $headers, int $column, string $direction): array
    {
        if (!isset($headers[$column])) {
            return $data;
        }

        $sortColumnHeader = $headers[$column];

        $sortedData = $data;
        usort($sortedData, function ($a, $b) use ($sortColumnHeader, $direction) {
            $valA = $a[$sortColumnHeader] ?? '';
            $valB = $b[$sortColumnHeader] ?? '';

            // 尝试将值转换为数字进行比较
            $numericA = is_numeric($valA) ? floatval($valA) : 0;
            $numericB = is_numeric($valB) ? floatval($valB) : 0;

            // 如果两个值都是数字，则按数字比较，否则按字符串比较
            if (is_numeric($valA) && is_numeric($valB)) {
                $result = $numericA <=> $numericB;
            } else {
                $result = strcasecmp((string)$valA, (string)$valB);
            }

            // 如果是降序，则反转结果
            return $direction === 'desc' ? -$result : $result;
        });

        return $sortedData;
    }

    /**
     * 刷新表格显示
     */
    public function refreshTable($pageSize = null): void
    {
        if ($this->tableModel && $this->handle) {
            // 重新获取配置的数据
            $data = $this->getConfig('data', []);
            $headers = $this->getConfig('headers', []);
            $options = $this->getConfig('options', []);
            
            // 更新原始数据和显示数据
            $this->originalData = $data;
            
            // 如果当前有排序，则应用排序
            if ($this->sortColumn !== null && $options['sortable']) {
                $this->displayData = $this->sortDataByColumnAndDirection($this->originalData, $headers, $this->sortColumn, $this->sortDirection);
            } else {
                $this->displayData = $data;
            }
            
            // 对于 libui，我们不能动态更改模型的行数，所以我们需要确保
            // 模型处理器总是使用最新的数据
            // 通知所有行已更改（使用当前 displayData 的大小）
            $totalRows = is_null($pageSize) ? count($this->displayData) : $pageSize;
            for ($i = 0; $i < $totalRows; $i++) {
                LibuiTable::modelRowChanged($this->tableModel, $i);
            }
        }
    }

    /**
     * 设置表格标题排序指示器
     */
    public function setHeaderSortIndicator(int $column, SortIndicator $direction): void
    {
        if ($this->handle) {
            LibuiTable::setHeaderSortIndicator($this->handle, $column, $direction);
        }
    }

    /**
     * 获取表格标题排序指示器
     */
    public function getHeaderSortIndicator(int $column): SortIndicator
    {
        if ($this->handle) {
            return LibuiTable::headerSortIndicator($this->handle, $column);
        }
        return SortIndicator::None;
    }
}