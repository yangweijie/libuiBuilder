<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Table;
use Kingbes\Libui\TableValueType;

/**
 * 表格构建器
 */
class TableBuilder extends ComponentBuilder
{
    /** @var array 表格列 */
    protected array $columns = [];

    /** @var array 表格数据 */
    protected array $data = [];

    /** @var callable|null 行选择回调 */
    protected $rowSelectedCallback = null;

    /**
     * 设置列
     *
     * @param array $columns 列名数组
     * @return $this
     */
    public function columns(array $columns): self
    {
        $this->columns = $columns;
        $this->config['columnCount'] = count($columns);
        return $this;
    }

    /**
     * 设置数据
     *
     * @param array $data 数据数组
     * @return $this
     */
    public function data(array $data): self
    {
        $this->data = $data;
        $this->config['rowCount'] = count($data);
        return $this;
    }

    /**
     * 注册行选中事件
     *
     * @param callable $callback 回调函数
     * @return $this
     */
    public function onRowSelected(callable $callback): self
    {
        $this->rowSelectedCallback = $callback;
        return $this;
    }

    /**
     * 构建表格组件
     *
     * @return CData 表格句柄
     */
    protected function buildComponent(): CData
    {
        $columnCount = $this->config['columnCount'] ?? 0;
        $rowCount = $this->config['rowCount'] ?? 0;

        if ($columnCount === 0 || $rowCount === 0) {
            throw new \InvalidArgumentException('表格必须有列和数据');
        }

        // 创建表格模型处理器
        $handler = Table::modelHandler(
            $columnCount,
            TableValueType::String,
            $rowCount,
            function($h, $row, $column) {
                return Table::createValueStr($this->data[$row][$column] ?? '');
            }
        );

        // 创建表格模型
        $model = Table::createModel($handler);

        // 添加文本列
        foreach ($this->columns as $index => $name) {
            Table::appendTextColumn($model, $name, $index, false);
        }

        // 创建表格
        $this->handle = Table::create($model, -1);

        return $this->handle;
    }

    /**
     * 构建后处理 - 绑定事件
     *
     * @return void
     */
    protected function afterBuild(): void
    {
        // 绑定行选择事件
        if ($this->rowSelectedCallback) {
            $callback = $this->rowSelectedCallback;
            $stateManager = $this->stateManager;
            
            Table::onRowClicked($this->handle, function($table, $row) use ($callback, $stateManager) {
                if ($stateManager) {
                    $callback($row, $this, $stateManager);
                } else {
                    $callback($row, $this);
                }
            });
        }
    }

    /**
     * 获取组件类型
     *
     * @return string
     */
    public function getType(): string
    {
        return 'table';
    }

    /**
     * 更新表格数据
     *
     * @param mixed $data 新数据
     * @return $this
     */
    public function setValue(mixed $data): self
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('TableBuilder setValue expects array data');
        }
        
        $this->data = $data;
        $this->config['rowCount'] = count($data);
        
        // 注意：实际更新需要重新构建或使用模型的行更新方法
        // 这里简化处理，实际使用时可能需要更复杂的更新逻辑
        
        return $this;
    }

    /**
     * 获取表格数据
     *
     * @return array
     */
    public function getValue(): array
    {
        return $this->data;
    }

    
}
