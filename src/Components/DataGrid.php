<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\Table as LibuiTable;
use Kingbes\Libui\TableValueType;
use Kingbes\Libui\SortIndicator;
use FFI\CData;

class DataGrid extends TableBuilder
{
    public $allData = [];      // 所有数据
    public $filteredData = []; // 过滤后的数据
    private $currentPage = 1;     // 当前页
    public $pageSize = 10;       // 每页大小
    private $totalPages = 1;      // 总页数
    private $filters = [];      // 过滤条件
    private $onNew = null;  // 新建回调
    private $onEdit = null; // 编辑回调
    private $onDelete = null; // 删除回调
    private $onSearch = null; // 搜索回调
    private $onPageChange = null; // 分页回调

    protected function getDefaultConfig(): array
    {
        return [
            'headers' => [],
            'data' => [],
            'options' => [
                'sortable' => true,
                'multiSelect' => false,
                'headerVisible' => true,
                'columnWidths' => [],
                'showPagination' => true,
                'showSearch' => true,
                'showCRUDButtons' => true,
                'pageSize' => 10
            ],
            'eventHandlers' => []
        ];
    }

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $options = $this->getConfig('options', []);
        $this->pageSize = $options['pageSize'] ?? 10;
    }

    protected function createNativeControl(): CData
    {
        $headers = $this->getConfig('headers', []);
        $data = $this->getConfig('data', []);
        $options = array_merge($this->getDefaultConfig()['options'], $this->getConfig('options', []));

        // 保存所有数据
        $this->originalData = $data; // 用于排序
        $this->allData = $data;      // 所有数据
        $this->filteredData = $data; // 过滤后的数据
        $this->totalPages = max(1, ceil(count($this->filteredData) / $this->pageSize));

        // 根据当前页获取要显示的数据
        $start = ($this->currentPage - 1) * $this->pageSize;
        $pageData = array_slice($this->filteredData, $start, $this->pageSize);
        $this->displayData = $pageData;

        $numColumns = count($headers);
        $numRows = count($pageData); // 实际当前页的数据行数
        
        // 创建表格模型处理器
        $this->tableHandler = \Kingbes\Libui\Table::modelHandler(
            $numColumns,
            \Kingbes\Libui\TableValueType::String,
            $numRows,
            function($handler, $row, $column) use ($headers, $pageData) {
                // 从当前页数据中获取值
                if (!isset($pageData[$row])) {
                    return \Kingbes\Libui\Table::createValueStr('');
                }
                
                $currentItem = $pageData[$row];
                
                if (isset($headers[$column])) {
                    $headerName = $headers[$column];
                    if (isset($currentItem[$headerName])) {
                        return \Kingbes\Libui\Table::createValueStr(strval($currentItem[$headerName]));
                    }
                }
                
                return \Kingbes\Libui\Table::createValueStr('');
            }
        );

        // 创建表格模型
        $this->tableModel = \Kingbes\Libui\Table::createModel($this->tableHandler);
        
        // 创建表格控件
        $tableControl = \Kingbes\Libui\Table::create($this->tableModel, -1);
        
        // 添加列到表格控件
        foreach ($headers as $index => $header) {
            \Kingbes\Libui\Table::appendTextColumn($tableControl, $header, $index, false);
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
        
        // 设置选择模式
        $selectionMode = $options['multiSelect'] ? 
            \Kingbes\Libui\TableSelectionMode::ZeroOrMany : \Kingbes\Libui\TableSelectionMode::One;
        \Kingbes\Libui\Table::setSelectionMode($this->handle, $selectionMode);
        
        // 设置列宽度
        foreach ($options['columnWidths'] as $columnIndex => $width) {
            \Kingbes\Libui\Table::setColumnWidth($this->handle, $columnIndex, $width);
        }
        
        // 添加事件监听
        foreach ($eventHandlers as $event => $handler) {
            if ($event === 'onRowClicked') {
                \Kingbes\Libui\Table::onRowClicked($this->handle, function($table, $row) use ($handler) {
                    $handler($this, $row);
                });
            } elseif ($event === 'onRowDoubleClicked') {
                \Kingbes\Libui\Table::onRowDoubleClicked($this->handle, function($table, $row) use ($handler) {
                    $handler($this, $row);
                });
            } elseif ($event === 'onSelectionChanged') {
                \Kingbes\Libui\Table::onSelectionChanged($this->handle, function($table) use ($handler) {
                    $handler($this);
                });
            } elseif ($event === 'onHeaderClicked') {
                \Kingbes\Libui\Table::onHeaderClicked($this->handle, function($table, $column) use ($handler, $options) {
                    $this->handleHeaderClick($column, $options['sortable']);
                    $handler($this, $column, $this->sortColumn, $this->sortDirection);
                });
            }
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
        \Kingbes\Libui\Table::setHeaderSortIndicator($this->handle, $column, $this->getSortEum());
        
        // 对所有数据进行排序
        $this->allData = $this->sortDataByColumnAndDirection($this->allData, $headers, $this->sortColumn, $this->sortDirection);
        $this->filteredData = $this->sortDataByColumnAndDirection($this->filteredData, $headers, $this->sortColumn, $this->sortDirection);
        
        // 重新设置当前页的数据并刷新
        $start = ($this->currentPage - 1) * $this->pageSize;
        $this->displayData = array_slice($this->filteredData, $start, $this->pageSize);
        $this->refreshTable();
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

    public function getSortEum(): \Kingbes\Libui\SortIndicator
    {
        return $this->sortDirection == 'asc'? \Kingbes\Libui\SortIndicator::Ascending:\Kingbes\Libui\SortIndicator::Descending;
    }

    function clearAllSortIndicators($table, $numColumns = 5): void
    {
        for ($i = 0; $i < $numColumns; $i++) {
            \Kingbes\Libui\Table::setHeaderSortIndicator($table, $i, \Kingbes\Libui\SortIndicator::None);
        }
    }

    /**
     * 刷新表格显示
     */
    public function refreshTable(): void
    {
        if ($this->tableModel && $this->handle) {
            // 通知当前页的每一行都已更改
            $displayRowCount = min($this->pageSize, count($this->displayData));
            for ($i = 0; $i < $displayRowCount; $i++) {
                \Kingbes\Libui\Table::modelRowChanged($this->tableModel, $i);
            }
        }
    }

    /**
     * 设置分页大小
     */
    public function setPageSize(int $size): self
    {
        $this->pageSize = $size;
        $this->updatePagination();
        return $this;
    }

    /**
     * 更新分页信息
     */
    private function updatePagination(): void
    {
        $this->totalPages = max(1, ceil(count($this->filteredData) / $this->pageSize));
        if ($this->currentPage > $this->totalPages && $this->totalPages > 0) {
            $this->currentPage = $this->totalPages;
        } elseif ($this->totalPages === 0) {
            $this->currentPage = 1;
        }
    }

    /**
     * 跳转到指定页
     */
    public function goToPage(int $page): self
    {
        if ($page >= 1 && $page <= $this->totalPages) {
            $this->currentPage = $page;
            $this->updatePageData();
            $this->refreshTable();
        }
        return $this;
    }

    /**
     * 下一页
     */
    public function nextPage(): self
    {
        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
            $this->updatePageData();
            $this->refreshTable();
        }
        return $this;
    }

    /**
     * 上一页
     */
    public function prevPage(): self
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->updatePageData();
            $this->refreshTable();
        }
        return $this;
    }

    /**
     * 更新页面数据
     */
    private function updatePageData(): void
    {
        $start = ($this->currentPage - 1) * $this->pageSize;
        $this->displayData = array_slice($this->filteredData, $start, $this->pageSize);
    }

    /**
     * 应用过滤器
     */
    public function applyFilter(string $filterText = ''): self
    {
        if (empty($filterText)) {
            $this->filteredData = $this->allData;
        } else {
            $filterText = strtolower($filterText);
            $this->filteredData = array_filter($this->allData, function($item) use ($filterText) {
                foreach ($item as $value) {
                    if (stripos((string)$value, $filterText) !== false) {
                        return true;
                    }
                }
                return false;
            });
            $this->filteredData = array_values($this->filteredData);
        }

        // 如果有排序设置，应用排序
        if ($this->sortColumn !== null) {
            $headers = $this->getConfig('headers', []);
            $this->filteredData = $this->sortDataByColumnAndDirection($this->filteredData, $headers, $this->sortColumn, $this->sortDirection);
        }

        $this->currentPage = 1;
        $this->updatePagination();
        $this->updatePageData();
        $this->refreshTable();
        
        return $this;
    }

    /**
     * 添加新记录
     */
    public function addRecord(array $record): self
    {
        $this->allData[] = $record;
        $this->filteredData[] = $record;
        $this->updatePagination();
        $this->refreshTable();
        return $this;
    }

    /**
     * 更新记录
     */
    public function updateRecord(int $index, array $record): self
    {
        if (isset($this->allData[$index])) {
            $this->allData[$index] = $record;
        }
        
        // 在过滤数据中查找并更新
        $id = $record['id'] ?? null;
        if ($id !== null) {
            foreach ($this->filteredData as $key => $item) {
                if (($item['id'] ?? null) == $id) {
                    $this->filteredData[$key] = $record;
                    break;
                }
            }
        }
        
        $this->refreshTable();
        return $this;
    }

    /**
     * 删除记录
     */
    public function deleteRecord(int $index): self
    {
        if (isset($this->allData[$index])) {
            $deletedRecord = $this->allData[$index];
            unset($this->allData[$index]);
            $this->allData = array_values($this->allData); // 重新索引
            
            // 从过滤数据中删除
            foreach ($this->filteredData as $key => $item) {
                if ($item === $deletedRecord) {
                    unset($this->filteredData[$key]);
                    break;
                }
            }
            $this->filteredData = array_values($this->filteredData); // 重新索引
            
            $this->updatePagination();
            $this->refreshTable();
        }
        return $this;
    }

    /**
     * 获取当前页数据
     */
    public function getCurrentPageData(): array
    {
        $start = ($this->currentPage - 1) * $this->pageSize;
        return array_slice($this->filteredData, $start, $this->pageSize);
    }

    /**
     * 获取总页数
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * 获取当前页
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * 获取记录总数
     */
    public function getTotalRecords(): int
    {
        return count($this->allData);
    }

    /**
     * 设置新建回调
     */
    public function onNew(callable $callback): self
    {
        $this->onNew = $callback;
        return $this;
    }

    /**
     * 设置编辑回调
     */
    public function onEdit(callable $callback): self
    {
        $this->onEdit = $callback;
        return $this;
    }

    /**
     * 设置删除回调
     */
    public function onDelete(callable $callback): self
    {
        $this->onDelete = $callback;
        return $this;
    }

    /**
     * 设置搜索回调
     */
    public function onSearch(callable $callback): self
    {
        $this->onSearch = $callback;
        return $this;
    }

    /**
     * 设置分页回调
     */
    public function onPageChange(callable $callback): self
    {
        $this->onPageChange = $callback;
        return $this;
    }
}