# TableBuilder 组件实现

## 概述

TableBuilder 是一个继承自 ComponentBuilder 的组件，用于封装 kingbes/libui 的 Table 功能，提供简化的表格控件使用方式。

## 功能特性

- **表头设置**：通过 `headers()` 方法设置表格列标题
- **数据绑定**：通过 `data()` 方法绑定表格数据
- **表格选项**：支持多种表格选项，包括排序、多选、表头可见性等
- **事件处理**：支持行点击、双击、选择变化和表头点击事件
- **列宽控制**：支持设置各列宽度
- **排序指示器**：支持设置和获取表头排序指示器

## 使用示例

```php
use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;

App::init();

$employees = [
    ['ID' => '1', 'Name' => 'John Doe', 'Position' => 'Developer', 'Salary' => '$75,000'],
    ['ID' => '2', 'Name' => 'Jane Smith', 'Position' => 'Designer', 'Salary' => '$65,000'],
    ['ID' => '3', 'Name' => 'Bob Johnson', 'Position' => 'Manager', 'Salary' => '$85,000'],
];

$window = Builder::window()
    ->title('Employee Table Example')
    ->size(800, 500)
    ->contains([
        Builder::vbox()->contains([
            Builder::label()
                ->text('Employee Information Table'),
            Builder::table()
                ->headers(['ID', 'Name', 'Position', 'Salary'])
                ->data($employees)
                ->options([
                    'sortable' => true,
                    'multiSelect' => false,
                    'headerVisible' => true,
                    'columnWidths' => [50, 200, 150, 100]
                ])
                ->onEvent('onRowClicked', function($table, $row) use ($employees) {
                    echo "Row clicked: $row\n";
                })
                ->onEvent('onHeaderClicked', function($table, $column) {
                    echo "Header clicked: $column\n";
                })
        ])
    ]);

$window->show();
App::run();
```

## API 方法

### `headers(array $headers): self`
设置表格列标题

### `data(array $data): self`
设置表格数据

### `options(array $options): self`
设置表格选项，包括：
- `sortable`: 是否可排序
- `multiSelect`: 是否支持多选
- `headerVisible`: 表头是否可见 (当前版本不支持动态设置)
- `columnWidths`: 各列宽度数组

### `onEvent(string $event, callable $handler): self`
添加事件处理器，支持的事件包括：
- `onRowClicked`: 行点击事件
- `onRowDoubleClicked`: 行双击事件
- `onSelectionChanged`: 选择变化事件
- `onHeaderClicked`: 表头点击事件 (function($table, $column, $sortColumn, $sortDirection))

注意：由于底层 UI 库的限制，Table 控件的数据模型无法动态更新。要实现动态排序，
需要在外部处理程序中重新创建整个表格组件。请参考 example/table_demo_with_sorting.php 示例。

### `getSelection()`
获取当前表格选择

### `setSelection($selection)`
设置表格选择

### `getSelectionMode()`
获取表格选择模式

### `setHeaderSortIndicator(int $column, SortIndicator $direction)`
设置表头排序指示器

### `getHeaderSortIndicator(int $column)`
获取表头排序指示器

## 实现细节

- TableBuilder 继承自 ComponentBuilder，遵循项目组件构建模式
- 使用 kingbes/libui 的底层 API 创建表格模型和控件
- 实现了完整的 getDefaultConfig、createNativeControl 和 applyConfig 方法
- 支持动态数据更新和事件处理
