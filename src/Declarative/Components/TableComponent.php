<?php

namespace Kingbes\Libui\Declarative\Components;

// 增强的多行文本框组件

use FFI\CData;
use Kingbes\Libui\Table;

class TableComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:table';
    }
    
    // 定义组件支持的属性
    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'data', 'columns', 'rows'
        ]);
    }

    public function render(): CData
    {
        // 表格需要更复杂的数据模型设置
        // 这里提供基础框架，实际使用需要根据具体数据结构调整
        $params = Table::newModelHandler();
        $model = Table::newModel($params);
        $table = Table::create($params);

        return $table;
    }

    public function getValue()
    {
        // TODO: Implement getValue() method.
    }

    public function setValue($value): void
    {
        // TODO: Implement setValue() method.
    }
}