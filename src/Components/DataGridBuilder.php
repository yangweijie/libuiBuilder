<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\Box;
use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\Control;
use Kingbes\Libui\Window;
use Kingbes\Libui\Table as LibuiTable;
use Kingbes\Libui\SortIndicator;
use FFI\CData;

/**
 * DataGridBuilder - 提供完整的数据网格功能，包括CRUD操作、分页、搜索和排序
 */
class DataGridBuilder extends ComponentBuilder
{
    // 数据管理
    private array $originalData = [];
    private array $filteredData = [];
    private array $pageData = [];
    
    // 分页控制
    private int $currentPage = 1;
    private int $pageSize = 10;
    private int $totalPages = 1;
    
    // 排序控制
    private ?int $sortColumn = null;
    private ?string $sortDirection = null;
    
    // 组件引用
    private ?TableBuilder $tableBuilder = null;
    private ?EntryBuilder $filterEntry = null;
    private ?LabelBuilder $pageLabel = null;
    private ?ButtonBuilder $prevButton = null;
    private ?ButtonBuilder $nextButton = null;
    private ?ButtonBuilder $newButton = null;
    private ?ButtonBuilder $editButton = null;
    private ?ButtonBuilder $deleteButton = null;
    private ?ButtonBuilder $clearSortButton = null;
    private ?int $selectedRow = -1;
    
    // 事件处理器
    private array $crudHandlers = [];

    /**
     * DataGrid 可以包含子组件
     */
    protected function canHaveChildren(): bool
    {
        return true;
    }

    protected function getDefaultConfig(): array
    {
        return [
            'headers' => [],
            'data' => [],
            'pageSize' => 10,
            'options' => [
                'sortable' => true,
                'searchable' => true,
                'showCrudButtons' => true,
                'showPagination' => true,
                'multiSelect' => false,
                'columnWidths' => []
            ],
            'labels' => [
                'filter' => 'Filter:',
                'search' => 'Search',
                'clear' => 'Clear',
                'new' => 'New',
                'edit' => 'Edit',
                'delete' => 'Delete',
                'clearSort' => 'Clear Sort',
                'previous' => 'Previous',
                'next' => 'Next',
                'pageInfo' => 'Page {current} of {total}'
            ],
            'eventHandlers' => []
        ];
    }

    protected function createNativeControl(): CData
    {
        // 初始化数据
        $this->originalData = $this->getConfig('data', []);
        $this->filteredData = $this->originalData;
        $this->pageSize = $this->getConfig('pageSize', 10);
        
        // 应用初始排序
        if ($this->getConfig('options')['sortable'] && $this->sortColumn !== null) {
            $this->applySorting();
        }
        
        // 计算分页
        $this->calculatePagination();
        
        // 创建主容器
        $mainBox = Builder::vbox();
        
        // 创建工具栏
        if ($this->getConfig('options')['searchable'] || $this->getConfig('options')['showCrudButtons']) {
            $toolbarBox = $this->createToolbar();
            $this->addChild($toolbarBox);
        }
        
        // 创建表格
        $this->tableBuilder = $this->createTable();
        $this->addChild($this->tableBuilder);
        
        // 创建分页控件
        if ($this->getConfig('options')['showPagination']) {
            $paginationBox = $this->createPagination();
            $this->addChild($paginationBox);
        }
        
        return $mainBox->build();
    }

    protected function applyConfig(): void
    {
        // 应用配置到各个子组件
        $this->applyCrudHandlers();
        
        // 确保表格数据正确设置和刷新
        if ($this->tableBuilder) {
            $this->refreshTable();
        }
    }

    protected function buildChildren(): void
    {
        // 使用 Box 的原生构建方法来添加子组件
        if ($this->handle && $this->children) {
            foreach ($this->children as $child) {
                $childHandle = $child->build();
                $stretchy = $child->getConfig('stretchy', false);
                
                // 根据子组件类型设置合适的 stretchy 值
                if ($child instanceof TableBuilder) {
                    $stretchy = true; // 表格应该拉伸
                } elseif ($child instanceof BoxBuilder) {
                    // 工具栏和分页栏不拉伸
                    $stretchy = false;
                }
                
                Box::append($this->handle, $childHandle, $stretchy);
            }
        }
    }

    /**
     * 创建工具栏
     */
    private function createToolbar(): ComponentBuilder
    {
        $toolbar = Builder::hbox();
        $labels = $this->getConfig('labels');
        
        // 搜索控件
        if ($this->getConfig('options')['searchable']) {
            $toolbar->addChild(Builder::label(['text' => $labels['filter']]));
            $this->filterEntry = Builder::entry();
            $toolbar->addChild($this->filterEntry);
            
            $searchBtn = Builder::button(['text' => $labels['search']]);
            $searchBtn->onClick(fn() => $this->handleSearch());
            $toolbar->addChild($searchBtn);
            
            $clearBtn = Builder::button(['text' => $labels['clear']]);
            $clearBtn->onClick(fn() => $this->handleClearFilter());
            $toolbar->addChild($clearBtn);
        }
        
        // CRUD 按钮
        if ($this->getConfig('options')['showCrudButtons']) {
            $this->newButton = Builder::button(['text' => $labels['new']]);
            $this->newButton->onClick(fn() => $this->handleNew());
            $toolbar->addChild($this->newButton);
            
            $this->editButton = Builder::button(['text' => $labels['edit']]);
            $this->editButton->onClick(fn() => $this->handleEdit());
            $toolbar->addChild($this->editButton);
            
            $this->deleteButton = Builder::button(['text' => $labels['delete']]);
            $this->deleteButton->onClick(fn() => $this->handleDelete());
            $toolbar->addChild($this->deleteButton);
            
            $this->clearSortButton = Builder::button(['text' => $labels['clearSort']]);
            $this->clearSortButton->onClick(fn() => $this->handleClearSort());
            $toolbar->addChild($this->clearSortButton);
        }
        
        return $toolbar;
    }

    /**
     * 创建表格
     */
    private function createTable(): TableBuilder
    {
        $table = Builder::table()
            ->headers($this->getConfig('headers'))
            ->data($this->pageData)
            ->options([
                'sortable' => $this->getConfig('options')['sortable'],
                'multiSelect' => $this->getConfig('options')['multiSelect'],
                'columnWidths' => $this->getConfig('options')['columnWidths'],
            ]);
        
        // 添加表格事件处理
        $table->onEvent('onRowClicked', function($tableBuilder, $row) {
            $this->selectedRow = $row;
            $this->emit('rowSelected', $row, $this->getCurrentPageData($row));
            $this->updateButtonStates();
        });
        
        $table->onEvent('onHeaderClicked', function($tableBuilder, $column, $sortColumn, $sortDirection) {
            if ($this->getConfig('options')['sortable']) {
                $this->sortColumn = $column;
                $this->sortDirection = $sortDirection;
                $this->applySortingAndRefresh();
                $this->emit('headerClicked', $column, $sortDirection);
            }
        });
        
        return $table;
    }

    /**
     * 创建分页控件
     */
    private function createPagination(): ComponentBuilder
    {
        $pagination = Builder::hbox();
        $labels = $this->getConfig('labels');
        
        $this->prevButton = Builder::button(['text' => $labels['previous']]);
        $this->prevButton->onClick(fn() => $this->handlePreviousPage());
        $pagination->addChild($this->prevButton);
        
        $this->pageLabel = Builder::label();
        $this->updatePageLabel();
        $pagination->addChild($this->pageLabel);
        
        $this->nextButton = Builder::button(['text' => $labels['next']]);
        $this->nextButton->onClick(fn() => $this->handleNextPage());
        $pagination->addChild($this->nextButton);
        
        return $pagination;
    }

    /**
     * 设置CRUD事件处理器
     */
    public function onNew(callable $handler): self
    {
        $this->crudHandlers['new'] = $handler;
        return $this;
    }

    public function onEdit(callable $handler): self
    {
        $this->crudHandlers['edit'] = $handler;
        return $this;
    }

    public function onDelete(callable $handler): self
    {
        $this->crudHandlers['delete'] = $handler;
        return $this;
    }

    /**
     * 应用CRUD处理器
     */
    private function applyCrudHandlers(): void
    {
        // 实际的处理器调用在事件处理方法中
    }

    /**
     * 搜索处理
     */
    private function handleSearch(): void
    {
        $filterText = $this->filterEntry ? $this->filterEntry->getValue() : '';
        
        if (empty($filterText)) {
            $this->filteredData = $this->originalData;
        } else {
            $filterText = strtolower($filterText);
            $this->filteredData = array_filter($this->originalData, function($item) use ($filterText) {
                foreach ($item as $value) {
                    if (stripos((string)$value, $filterText) !== false) {
                        return true;
                    }
                }
                return false;
            });
            $this->filteredData = array_values($this->filteredData);
        }
        
        $this->currentPage = 1;
        $this->applySortingAndRefresh();
        $this->emit('search', $filterText, count($this->filteredData));
    }

    /**
     * 清除过滤
     */
    private function handleClearFilter(): void
    {
        if ($this->filterEntry) {
            $this->filterEntry->setValue('');
        }
        $this->filteredData = $this->originalData;
        $this->currentPage = 1;
        $this->applySortingAndRefresh();
        $this->emit('filterCleared');
    }

    /**
     * 新增处理
     */
    private function handleNew(): void
    {
        if (isset($this->crudHandlers['new'])) {
            $handler = $this->crudHandlers['new'];
            $handler($this);
        } else {
            $this->showDefaultNewDialog();
        }
    }

    /**
     * 编辑处理
     */
    private function handleEdit(): void
    {
        if ($this->selectedRow < 0) {
            $this->emit('noSelection', 'edit');
            return;
        }
        
        $currentData = $this->getCurrentPageData($this->selectedRow);
        if (!$currentData) {
            $this->emit('dataNotFound', 'edit');
            return;
        }
        
        if (isset($this->crudHandlers['edit'])) {
            $handler = $this->crudHandlers['edit'];
            $handler($this, $currentData, $this->selectedRow);
        } else {
            $this->showDefaultEditDialog($currentData);
        }
    }

    /**
     * 删除处理
     */
    private function handleDelete(): void
    {
        if ($this->selectedRow < 0) {
            $this->emit('noSelection', 'delete');
            return;
        }
        
        $currentData = $this->getCurrentPageData($this->selectedRow);
        if (!$currentData) {
            $this->emit('dataNotFound', 'delete');
            return;
        }
        
        if (isset($this->crudHandlers['delete'])) {
            $handler = $this->crudHandlers['delete'];
            $handler($this, $currentData, $this->selectedRow);
        } else {
            $this->performDefaultDelete($currentData);
        }
    }

    /**
     * 清除排序
     */
    private function handleClearSort(): void
    {
        $this->sortColumn = null;
        $this->sortDirection = null;
        
        if ($this->tableBuilder && $this->tableBuilder->handle) {
            $headers = $this->getConfig('headers');
            for ($i = 0; $i < count($headers); $i++) {
                $this->tableBuilder->setHeaderSortIndicator($i, SortIndicator::None);
            }
        }
        
        $this->applySortingAndRefresh();
        $this->emit('sortCleared');
    }

    /**
     * 上一页
     */
    private function handlePreviousPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->refreshTable();
            $this->emit('pageChanged', $this->currentPage);
        }
    }

    /**
     * 下一页
     */
    private function handleNextPage(): void
    {
        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
            $this->refreshTable();
            $this->emit('pageChanged', $this->currentPage);
        }
    }

    /**
     * 应用排序
     */
    private function applySorting(): void
    {
        if ($this->sortColumn === null || $this->sortDirection === null) {
            return;
        }
        
        $headers = $this->getConfig('headers');
        if (!isset($headers[$this->sortColumn])) {
            return;
        }
        
        $headerKey = $headers[$this->sortColumn];
        
        usort($this->filteredData, function($a, $b) use ($headerKey) {
            // 尝试多种键名匹配方式
            $valA = $a[$headerKey] ?? $a[strtolower($headerKey)] ?? $a[strtoupper($headerKey)] ?? '';
            $valB = $b[$headerKey] ?? $b[strtolower($headerKey)] ?? $b[strtoupper($headerKey)] ?? '';
            
            // 尝试数字比较
            if (is_numeric($valA) && is_numeric($valB)) {
                $result = floatval($valA) <=> floatval($valB);
            } else {
                // 字符串比较
                $result = strcasecmp((string)$valA, (string)$valB);
            }
            
            return $this->sortDirection === 'desc' ? -$result : $result;
        });
    }

    /**
     * 应用排序并刷新
     */
    private function applySortingAndRefresh(): void
    {
        $this->applySorting();
        $this->calculatePagination();
        $this->refreshTable();
    }

    /**
     * 计算分页
     */
    private function calculatePagination(): void
    {
        $this->totalPages = max(1, ceil(count($this->filteredData) / $this->pageSize));
        $this->currentPage = max(1, min($this->currentPage, $this->totalPages));
        
        $start = ($this->currentPage - 1) * $this->pageSize;
        $pageItems = array_slice($this->filteredData, $start, $this->pageSize);
        
        // 将关联数组转换为索引数组，TableBuilder 需要索引数组格式
        $headers = $this->getConfig('headers', []);
        $this->pageData = [];
        
        foreach ($pageItems as $item) {
            $row = [];
            foreach ($headers as $header) {
                // 尝试直接匹配，然后尝试小写匹配
                $value = $item[$header] ?? $item[strtolower($header)] ?? $item[strtoupper($header)] ?? '';
                $row[] = $value;
            }
            $this->pageData[] = $row;
        }
    }

    /**
     * 刷新表格
     */
    private function refreshTable(): void
    {
        $this->calculatePagination();
        $this->updatePageLabel();
        
        if ($this->tableBuilder) {
            $this->tableBuilder->data($this->pageData);
            $this->tableBuilder->refreshTable($this->pageSize);
        }
        
        $this->updateButtonStates();
    }

    /**
     * 刷新表格显示（不重新计算分页）
     */
    private function refreshTableDisplay(): void
    {
        // 重新计算当前页的数据
        $start = ($this->currentPage - 1) * $this->pageSize;
        $pageItems = array_slice($this->filteredData, $start, $this->pageSize);
        
        // 将关联数组转换为索引数组
        $headers = $this->getConfig('headers', []);
        $this->pageData = [];
        
        foreach ($pageItems as $item) {
            $row = [];
            foreach ($headers as $header) {
                $value = $item[$header] ?? $item[strtolower($header)] ?? $item[strtoupper($header)] ?? '';
                $row[] = $value;
            }
            $this->pageData[] = $row;
        }
        
        $this->updatePageLabel();
        
        if ($this->tableBuilder) {
            $this->tableBuilder->data($this->pageData);
            $this->tableBuilder->refreshTable($this->pageSize);
        }
        
        $this->updateButtonStates();
    }

    /**
     * 更新页面标签
     */
    private function updatePageLabel(): void
    {
        if ($this->pageLabel) {
            $template = $this->getConfig('labels')['pageInfo'];
            $text = str_replace(['{current}', '{total}'], [$this->currentPage, $this->totalPages], $template);
            $this->pageLabel->setValue($text);
        }
    }

    /**
     * 更新按钮状态
     */
    private function updateButtonStates(): void
    {
        // 注意：libui 可能没有直接的 setEnabled 方法
        // 这里通过配置来控制按钮状态，具体实现可能需要根据 libui 的 API 调整
        if ($this->prevButton) {
            $this->prevButton->setConfig('enabled', $this->currentPage > 1);
        }
        
        if ($this->nextButton) {
            $this->nextButton->setConfig('enabled', $this->currentPage < $this->totalPages);
        }
        
        if ($this->editButton) {
            $this->editButton->setConfig('enabled', $this->selectedRow >= 0);
        }
        
        if ($this->deleteButton) {
            $this->deleteButton->setConfig('enabled', $this->selectedRow >= 0);
        }
        
        if ($this->clearSortButton) {
            $this->clearSortButton->setConfig('enabled', $this->sortColumn !== null);
        }
    }

    /**
     * 获取当前页的指定行数据
     */
    private function getCurrentPageData(int $row): ?array
    {
        if (!isset($this->pageData[$row])) {
            return null;
        }
        
        // 计算当前页在过滤数据中的起始位置
        $start = ($this->currentPage - 1) * $this->pageSize;
        
        // 尝试直接通过索引获取数据
        if (isset($this->filteredData[$start + $row])) {
            return $this->filteredData[$start + $row];
        }
        
        // 如果直接索引失败，尝试通过匹配表格数据来找到对应的原始数据
        $headers = $this->getConfig('headers', []);
        $rowData = $this->pageData[$row];
        
        // 在过滤数据中搜索匹配的记录
        foreach ($this->filteredData as $item) {
            $match = true;
            foreach ($headers as $index => $header) {
                $tableValue = $rowData[$index] ?? '';
                $itemValue = $item[$header] ?? $item[strtolower($header)] ?? $item[strtoupper($header)] ?? '';
                if ((string)$tableValue !== (string)$itemValue) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                return $item;
            }
        }
        
        return null;
    }

    /**
     * 显示默认的新增对话框
     */
    private function showDefaultNewDialog(): void
    {
        // 这里可以实现一个简单的默认新增对话框
        // 或者委托给自定义处理器
        $this->emit('showNewDialog');
    }

    /**
     * 显示默认的编辑对话框
     */
    private function showDefaultEditDialog(array $data): void
    {
        // 这里可以实现一个简单的默认编辑对话框
        // 或者委托给自定义处理器
        $this->emit('showEditDialog', $data);
    }

    /**
     * 执行默认删除
     */
    private function performDefaultDelete(array $data): void
    {
        $id = $data['id'] ?? null;
        if ($id === null) {
            return;
        }
        
        // 从原始数据中删除
        $this->originalData = array_filter($this->originalData, function($item) use ($id) {
            return $item['id'] !== $id;
        });
        $this->originalData = array_values($this->originalData);
        
        // 从过滤数据中删除
        $this->filteredData = array_filter($this->filteredData, function($item) use ($id) {
            return $item['id'] !== $id;
        });
        $this->filteredData = array_values($this->filteredData);
        
        $this->selectedRow = -1;
        $this->applySortingAndRefresh();
        $this->emit('deleted', $data);
    }

    // ========== 公共API方法 ==========

    /**
     * 更新数据
     */
    public function updateData(array $data): void
    {
        $this->originalData = $data;
        $this->filteredData = $data;
        $this->currentPage = 1;
        $this->applySortingAndRefresh();
        $this->emit('dataUpdated', $data);
    }

    /**
     * 添加新数据
     */
    public function addData(array $item): void
    {
        // 记录添加前的总页数
        $oldTotalPages = $this->totalPages;
        $wasOnLastPage = ($this->currentPage == $oldTotalPages);
        
        $this->originalData[] = $item;
        $this->filteredData[] = $item;
        
        // 应用排序（如果有的话）
        $this->applySorting();
        
        // 重新计算分页
        $this->totalPages = max(1, ceil(count($this->filteredData) / $this->pageSize));
        
        // 如果新增数据导致页数增加，且原来在最后一页，则跳转到新页
        if ($this->totalPages > $oldTotalPages && $wasOnLastPage) {
            $this->currentPage = $this->totalPages;
        }
        
        // 刷新表格显示
        $this->refreshTableDisplay();
        
        $this->emit('dataAdded', $item);
    }

    /**
     * 更新指定数据
     */
    public function updateItem($id, array $newData): void
    {
        foreach ($this->originalData as &$item) {
            if ($item['id'] === $id) {
                $item = array_merge($item, $newData);
                break;
            }
        }
        
        foreach ($this->filteredData as &$item) {
            if ($item['id'] === $id) {
                $item = array_merge($item, $newData);
                break;
            }
        }
        
        $this->applySortingAndRefresh();
        $this->emit('dataUpdated', $id, $newData);
    }

    /**
     * 删除数据
     */
    public function deleteItem($id): void
    {
        $this->originalData = array_filter($this->originalData, function($item) use ($id) {
            return $item['id'] !== $id;
        });
        $this->originalData = array_values($this->originalData);
        
        $this->filteredData = array_filter($this->filteredData, function($item) use ($id) {
            return $item['id'] !== $id;
        });
        $this->filteredData = array_values($this->filteredData);
        
        // 应用排序
        $this->applySorting();
        
        // 计算删除后的总页数
        $totalPagesAfterDelete = max(1, ceil(count($this->filteredData) / $this->pageSize));
        
        // 如果当前页超过新的总页数，则返回到最后一页
        if ($this->currentPage > $totalPagesAfterDelete && $totalPagesAfterDelete >= 1) {
            $this->currentPage = $totalPagesAfterDelete;
        } else if ($totalPagesAfterDelete == 1 && count($this->filteredData) == 0) {
            // 如果删除后没有数据了，回到第一页
            $this->currentPage = 1;
        }
        
        // 更新总页数
        $this->totalPages = $totalPagesAfterDelete;
        
        // 重置选中行
        $this->selectedRow = -1;
        
        // 刷新显示
        $this->refreshTableDisplay();
        
        $this->emit('dataDeleted', $id);
    }

    /**
     * 获取当前选中的数据
     */
    public function getSelectedData(): ?array
    {
        return $this->getCurrentPageData($this->selectedRow);
    }

    /**
     * 获取指定行的数据（公共方法）
     */
    public function getRowData(int $row): ?array
    {
        return $this->getCurrentPageData($row);
    }

    /**
     * 获取所有数据
     */
    public function getAllData(): array
    {
        return $this->originalData;
    }

    /**
     * 获取过滤后的数据
     */
    public function getFilteredData(): array
    {
        return $this->filteredData;
    }

    /**
     * 设置页面大小
     */
    public function setPageSize(int $size): void
    {
        $this->pageSize = $size;
        $this->currentPage = 1;
        $this->applySortingAndRefresh();
    }

    /**
     * 获取当前页码
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * 获取总页数
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }
}