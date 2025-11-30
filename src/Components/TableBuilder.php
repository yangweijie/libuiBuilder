<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\Table;
use Kingbes\Libui\TableValueType;
use Closure;
use FFI\CData;

class TableBuilder extends ComponentBuilder
{
    private array $data = [];
    private array $columns = [];
    // 持有 FFI 回调以避免 PHP 回收闭包导致回调指针失效
    private array $ffiCallbacks = [];
    // 可选文件日志路径（为空则不写）
    private string $debugLogFile = '';
    private $model = null; // 保存表格模型引用
    // 保存原始 uiTableModelHandler CData，防止被 GC 回收
    private $cHandler = null;
    private $dataRef = null; // 数据引用，用于回调函数
    private int $selectedRow = -1; // 当前选中的行索引

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
        // Set debug log file for ffi callbacks (helps capture callback invocation in GUI mode)
        $this->debugLogFile = sys_get_temp_dir() . '/libui_table_builder.log';
        @file_put_contents($this->debugLogFile, "\n--- createNativeControl at " . date('c') . " ---\n", FILE_APPEND);

        // 检查是否绑定了状态，如果有则优先使用状态管理器中的数据
        if ($this->boundState) {
            $stateManager = StateManager::instance();
            $stateData = $stateManager->get($this->boundState);
            if ($stateData !== null && is_array($stateData)) {
                $this->data = $stateData;
            } else {
                $this->data = $this->getConfig('data');
            }
        } else {
            $this->data = $this->getConfig('data');
        }

        // 创建数据引用，回调函数将使用这个引用
        $this->dataRef = &$this->data;

        // 创建表格模型处理程序 - 将回调保存在 $this->ffiCallbacks 以防止被 GC 回收
        $this->ffiCallbacks['NumColumns'] = function ($h, $m) {
            return count($this->columns);
        };

        $this->ffiCallbacks['ColumnType'] = function ($h, $m, $i) {
            // 所有列默认字符串类型
            return TableValueType::String->value;
        };

        $this->ffiCallbacks['NumRows'] = function ($h, $m) {
            // 使用数据引用的当前行数
            return is_array($this->dataRef) ? count($this->dataRef) : 0;
        };

        // 使用类方法作为回调，生成持久的 Closure 引用
        $this->ffiCallbacks['CellValue'] =

            Closure::fromCallable([$this, 'ffiCellValue']);

        // SetCellValue 使用类方法
        $this->ffiCallbacks['SetCellValue'] =
            Closure::fromCallable([$this, 'ffiSetCellValue']);

        // 直接传入已持有的闭包，保证签名与 modelHandler 期望一致
        $modelHandler = Table::modelHandler(
            count($this->columns),
            TableValueType::String,
            is_array($this->dataRef) ? count($this->dataRef) : 0,
            $this->ffiCallbacks['CellValue'],
            $this->ffiCallbacks['SetCellValue']
        );

        // 保存原始 handler CData 防止被回收，然后创建表格模型并保存引用
        $this->cHandler = $modelHandler;
        $this->model = Table::createModel($this->cHandler);

        // 创建表格控件 - 行背景颜色列设为-1（无颜色列）
        $table = Table::create($this->model, -1);
        // 保存原生控件句柄，供 applyConfig 等后续调用使用
        $this->handle = $table;

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
    
    // 使用数据引用的获取单元格值回调
    public function getCellValueWithRef(CData $model, int $row, int $column): CData
    {
        // 调试日志：打印行列信息以便排查崩溃来源
        // 注意：在 GUI 线程中频繁打印可能会影响性能，仅用于调试
        echo "getCellValueWithRef called: row={$row}, col={$column}, dataRows=" . count($this->dataRef) . "\n";
        if (!isset($this->dataRef[$row][$column])) {
            echo "  -> cell not set, returning empty string\n";
            return Table::createValueStr('');
        }

        $value = $this->dataRef[$row][$column];

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
    
    public function bind(string $key): static
    {
        // 使用 ComponentBuilder 的绑定逻辑，这会设置 $this->boundState 并注册 StateManager 监听
        parent::bind($key);
        return $this;
    }

    /**
     * 更新表格数据并刷新显示
     */
    public function refreshData(): void
    {
        // 数据通过引用自动更新，无需手动刷新
        echo "表格数据已更新，共 " . count($this->data) . " 行\n";
    }
    
    /**
     * 实现setValue方法以支持数据绑定（使用增量更新）
     */
    public function setValue($value): void
    {
        if (is_array($value)) {
            // 如果组件还未构建，只更新数据，不调用UI更新
            if ($this->handle === null || $this->model === null) {
                echo "表格数据已保存，等待组件构建后显示\n";
                $this->data = $value;
                $this->dataRef = &$this->data;
                $this->setConfig('data', $value);
                return;
            }
            
            // 保存旧数据用于比较
            $oldData = $this->data;
            $oldCount = count($oldData);
            $newCount = count($value);
            
            echo "表格setValue: 旧数据{$oldCount}行, 新数据{$newCount}行";
            if ($oldCount > 0 && isset($oldData[0])) {
                echo ", 旧数据第一行: " . json_encode($oldData[0], JSON_UNESCAPED_UNICODE);
            }
            if ($newCount > 0 && isset($value[0])) {
                echo ", 新数据第一行: " . json_encode($value[0], JSON_UNESCAPED_UNICODE);
            }
            echo "\n";
            
            // 更新数据数组
            $this->data = $value;
            $this->dataRef = &$this->data;
            $this->setConfig('data', $value);
            
            if ($newCount === $oldCount) {
                // 数量相同，检查是否有数据更新
                $hasChanges = false;
                for ($i = 0; $i < $newCount; $i++) {
                    if (!isset($oldData[$i]) || $oldData[$i] !== $value[$i]) {
                        $hasChanges = true;
                        // 数据不同，调用updateRow
                        $this->updateRow($i, $value[$i]);
                    }
                }
                if (!$hasChanges) {
                    echo "数据未变化，无需更新UI\n";
                }
            } elseif ($newCount > $oldCount) {
                // 新增行
                for ($i = $oldCount; $i < $newCount; $i++) {
                    $this->insertRow($i, $value[$i]);
                }
            } else {
                // 删除行（从后往前删）
                for ($i = $oldCount - 1; $i >= $newCount; $i--) {
                    $this->deleteRow($i);
                }
            }
        }
    }
    
    /**
     * 插入新行
     */
    public function insertRow(int $index, array $rowData): void
    {
        // 只更新UI显示，不修改数据数组
        Table::modelRowInserted($this->model, $index);
        echo "插入行 {$index}: " . json_encode($rowData) . "\n";
    }
    
    /**
     * 更新行数据
     */
    public function updateRow(int $index, array $rowData): void
    {
        // 只更新UI显示，不修改数据数组
        Table::modelRowChanged($this->model, $index);
        echo "更新行 {$index}: " . json_encode($rowData) . "\n";
    }
    
    /**
     * 删除行
     */
    public function deleteRow(int $index): void
    {
        // 只更新UI显示，不修改数据数组
        Table::modelRowDeleted($this->model, $index);
        echo "删除行 {$index}\n";
    }
    
    /**
     * 设置选中行
     */
    public function setSelectedRow(int $index): void
    {
        // 从StateManager获取最新数据长度
        $stateManager = StateManager::instance();
        $tableData = $stateManager->get('tableData', []);
        $dataCount = count($tableData);
        
        if ($index >= 0 && $index < $dataCount) {
            $this->selectedRow = $index;
        }
    }
    
    /**
     * 获取选中行
     */
    public function getSelectedRow(): int
    {
        return $this->selectedRow;
    }
    
    /**
     * 获取选中行的数据
     */
    public function getSelectedRowData(): ?array
    {
        // 从StateManager获取最新数据，确保同步
        $stateManager = StateManager::instance();
        $tableData = $stateManager->get('tableData', []);
        
        if ($this->selectedRow >= 0 && $this->selectedRow < count($tableData)) {
            return $tableData[$this->selectedRow];
        }
        
        return null;
    }
    
    /**
     * 确保选中行索引有效
     */
    public function validateSelectedRow(): void
    {
        // 从StateManager获取最新数据长度
        $stateManager = StateManager::instance();
        $tableData = $stateManager->get('tableData', []);
        
        if ($this->selectedRow >= count($tableData)) {
            $this->selectedRow = -1; // 重置选择
        }
    }

    // 实际传给 FFI 的回调实现：签名与 modelHandler 中调用一致
    // modelHandler 会以 ($handler, $row, $column) 调用我们的 CellValue
    public function ffiCellValue($handler, $row, $column)
    {
        // 不直接 echo，改为可选文件日志以避免 GUI 线程干扰
        if ($this->debugLogFile) {
            @file_put_contents($this->debugLogFile, "[ffi CellValue] row={$row}, col={$column}, dataRows=" . (is_array($this->dataRef) ? count($this->dataRef) : 0) . "\n", FILE_APPEND);
        }

        if (!is_int($row) || !is_int($column) || $row < 0 || $column < 0) {
            return Table::createValueStr('');
        }
        if (!is_array($this->dataRef) || $row >= count($this->dataRef)) {
            return Table::createValueStr('');
        }
        if (!isset($this->dataRef[$row][$column])) {
            return Table::createValueStr('');
        }
        $val = $this->dataRef[$row][$column];
        if (is_string($val)) return Table::createValueStr($val);
        if (is_int($val)) return Table::createValueInt($val);
        if (is_bool($val)) return Table::createValueInt($val ? 1 : 0);
        return Table::createValueStr((string)$val);
    }

    // modelHandler 会以 ($handler, $row, $column, $v) 调用我们的 SetCellValue
    public function ffiSetCellValue($handler, $row, $column, $v)
    {
        if ($this->debugLogFile) {
            @file_put_contents($this->debugLogFile, "[ffi SetCellValue] row={$row}, col={$column}\n", FILE_APPEND);
        }
        if (!is_int($row) || !is_int($column) || $row < 0 || $column < 0) return;
        if (!is_array($this->data)) return;
        $type = Table::getValueType($v);
        switch ($type) {
            case TableValueType::String:
                $this->data[$row][$column] = Table::valueStr($v);
                break;
            case TableValueType::Int:
                $this->data[$row][$column] = Table::valueInt($v);
                break;
            default:
                $this->data[$row][$column] = '';
        }
    }
}
