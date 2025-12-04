<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\Box;
use Kingbes\Libui\Button;
use Kingbes\Libui\Control;
use Kingbes\Libui\Entry;
use Kingbes\Libui\Label;
use Kingbes\Libui\Spinbox;
use Kingbes\Libui\Table;
use Kingbes\Libui\TableValueType;
use Kingbes\Libui\Image;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\Window;

App::init();

// 创建状态管理器
$state = StateManager::instance();

// 模拟数据
$allData = [];
for ($i = 1; $i <= 100; $i++) {
    $allData[] = [
        'id' => $i,
        'name' => "Employee " . chr(65 + ($i % 26)) . ($i > 26 ? strval(intval($i/26)) : ''),
        'email' => "employee{$i}@company.com",
        'department' => ['Engineering', 'Sales', 'Marketing', 'HR', 'Finance', 'Operations'][rand(0, 5)],
        'salary' => rand(40000, 150000),
        'active' => rand(0, 1) == 1,
    ];
}

// 初始状态
$state->set('allData', $allData);
$state->set('currentPage', 1);
$state->set('pageSize', 10);
$state->set('filterText', '');
$state->set('sortColumn', -1);
$state->set('sortDirection', 1); // 1 for ascending, -1 for descending
$state->set('filteredData', $allData);

// 数据处理函数
function updateFilteredData($state): void
{
    $allData = $state->get('allData', []);
    $filterText = $state->get('filterText', '');
    
    if (empty($filterText)) {
        $filteredData = $allData;
    } else {
        $filteredData = array_filter($allData, function($row) use ($filterText) {
            foreach ($row as $value) {
                if (stripos(strval($value), $filterText) !== false) {
                    return true;
                }
            }
            return false;
        });
        $filteredData = array_values($filteredData); // 重新索引
    }
    
    // 应用排序
    $sortColumn = $state->get('sortColumn', -1);
    $sortDirection = $state->get('sortDirection', 1);
    if ($sortColumn >= 0) {
        usort($filteredData, function($a, $b) use ($sortColumn, $sortDirection) {
            $keys = array_keys($a);
            $sortKey = $keys[$sortColumn];
            
            $valA = $a[$sortKey];
            $valB = $b[$sortKey];
            
            if (is_numeric($valA) && is_numeric($valB)) {
                return ($valA <=> $valB) * $sortDirection;
            }
            
            return strcasecmp($valA, $valB) * $sortDirection;
        });
    }
    
    $state->set('filteredData', $filteredData);
}

// 翻页函数
function goToPage($page, $state): void
{
    $filteredData = $state->get('filteredData', []);
    $pageSize = $state->get('pageSize', 10);
    $totalPages = max(1, ceil(count($filteredData) / $pageSize));
    
    $page = max(1, min($page, $totalPages));
    $state->set('currentPage', $page);
}

// 搜索处理
function doSearch($state): void
{
    updateFilteredData($state);
    $state->set('currentPage', 1); // 重置到第一页
}

// 排序处理
function doSort($column, $state): void
{
    $currentSortColumn = $state->get('sortColumn', -1);
    $currentDirection = $state->get('sortDirection', 1);
    
    if ($currentSortColumn === $column) {
        // 切换排序方向
        $newDirection = $currentDirection * -1;
        $state->set('sortDirection', $newDirection);
    } else {
        // 新的排序列，升序开始
        $state->set('sortColumn', $column);
        $state->set('sortDirection', 1);
    }
    
    updateFilteredData($state);
    $state->set('currentPage', 1); // 重置到第一页
}

// 刷新表格函数
function refreshTable($model, $state): void
{
    if ($model) {
        // 获取当前页面的行数并通知所有行已更改
        $filteredData = $state->get('filteredData', []);
        $currentPage = $state->get('currentPage', 1);
        $pageSize = $state->get('pageSize', 10);
        
        $start = ($currentPage - 1) * $pageSize;
        $end = min($start + $pageSize, count($filteredData));
        
        // 通知所有可见行已更改
        for ($i = $start; $i < $end; $i++) {
            Table::modelRowChanged($model, $i - $start); // 使用相对于当前页的索引
        }
    }
}

// 当前模型引用
$modelRef = null;

// 表格回调函数
$ffiCallbacks = [];
$ffiCallbacks['NumColumns'] = function ($h, $m) {
    return 6; // ID, Name, Email, Department, Salary, Active
};

$ffiCallbacks['ColumnType'] = function ($h, $m, $i) {
    switch ($i) {
        case 5: // Active column - checkbox
            return TableValueType::Int->value;
        case 4: // Salary column - int for progress bar
            return TableValueType::Int->value;
        default:
            return TableValueType::String->value;
    }
};

$ffiCallbacks['NumRows'] = function ($h, $m) use ($state) {
    $filteredData = $state->get('filteredData', []);
    $currentPage = $state->get('currentPage', 1);
    $pageSize = $state->get('pageSize', 10);
    
    $start = ($currentPage - 1) * $pageSize;
    $end = min($start + $pageSize, count($filteredData));
    
    return $end - $start;
};

$ffiCallbacks['CellValue'] = function ($h, $row, $col) use ($state) {
    $filteredData = $state->get('filteredData', []);
    $currentPage = $state->get('currentPage', 1);
    $pageSize = $state->get('pageSize', 10);
    
    $start = ($currentPage - 1) * $pageSize;
    $actualRow = $start + $row;
    
    if (!isset($filteredData[$actualRow])) {
        return Table::createValueStr('');
    }
    
    $item = $filteredData[$actualRow];
    $values = array_values($item);
    
    if ($col >= count($values)) {
        return Table::createValueStr('');
    }
    
    $value = $values[$col];
    
    switch ($col) {
        case 5: // Active column (checkbox)
            return Table::createValueInt($value ? 1 : 0);
        case 4: // Salary column - use as progress value
            $maxSalary = 150000;
            $progress = min(100, max(0, intval(($value / $maxSalary) * 100)));
            return Table::createValueInt($progress);
        default:
            return Table::createValueStr(strval($value));
    }
};

$ffiCallbacks['SetCellValue'] = function ($h, $row, $col, $v) use ($state) {
    // 处理单元格编辑
    echo "SetCellValue called: row={$row}, col={$col}
"; // 调试信息
    $filteredData = $state->get('filteredData', []);

    $currentPage = $state->get('currentPage', 1);
    $pageSize = $state->get('pageSize', 10);
    
    $start = ($currentPage - 1) * $pageSize;
    $actualRow = $start + $row;
    
    if (!isset($filteredData[$actualRow])) {
        echo "Error: Row {$actualRow} not found in filtered data\n";
        return;
    }
    
    $type = Table::getValueType($v);
    
    switch ($type) {
        case TableValueType::String:
            $newValue = Table::valueStr($v);
            break;
        case TableValueType::Int:
            $newValue = Table::valueInt($v);
            break;
        default:
            $newValue = '';
    }
    
    echo "New value: '{$newValue}' (type: {$type->name})\n"; // 调试信息
    
    // 获取当前项的数据
    $currentItem = $filteredData[$actualRow];
    $allData = $state->get('allData', []);
    
    // 通过ID查找原始数据项
    $itemId = $currentItem['id']; // 假设每个数据项都有id字段
    
    echo "Looking for item with ID: {$itemId}\n"; // 调试信息
    
    // 查找原始数据中的对应项
    $originalIndex = null;
    foreach ($allData as $index => $item) {
        if ($item['id'] == $itemId) {
            $originalIndex = $index;
            break;
        }
    }
    
    if ($originalIndex !== null) {
        // 获取数据项的键名
        $dataKeys = array_keys($allData[$originalIndex]);
        if (isset($dataKeys[$col])) {
            $keyToModify = $dataKeys[$col];
            
            echo "Before update - AllData[{$originalIndex}][{$keyToModify}]: " . (isset($allData[$originalIndex][$keyToModify]) ? $allData[$originalIndex][$keyToModify] : 'NULL') . "\n";
            
            // 更新原始数据
            $allData[$originalIndex][$keyToModify] = $newValue;
            $state->set('allData', $allData);
            
            // 同时更新当前页的过滤数据
            $filteredData[$actualRow][$keyToModify] = $newValue;
            $state->set('filteredData', $filteredData);
            
            echo "Updated {$keyToModify} to: '{$newValue}' (ID: {$itemId}, Row: {$actualRow}, Col: {$col})
"; // 调试信息
            echo "Before update - AllData[{$originalIndex}][{$keyToModify}]: " . (isset($allData[$originalIndex][$keyToModify]) ? $allData[$originalIndex][$keyToModify] : 'NULL') . "
";
            echo "After update - AllData[{$originalIndex}][{$keyToModify}]: " . (isset($allData[$originalIndex][$keyToModify]) ? $allData[$originalIndex][$keyToModify] : 'NULL') . "
";
            echo "Before update - FilteredData[{$actualRow}][{$keyToModify}]: " . (isset($filteredData[$actualRow][$keyToModify]) ? $filteredData[$actualRow][$keyToModify] : 'NULL') . "
";
            echo "After update - FilteredData[{$actualRow}][{$keyToModify}]: " . (isset($filteredData[$actualRow][$keyToModify]) ? $filteredData[$actualRow][$keyToModify] : 'NULL') . "
";
        }
    }
};

// 简化的模型处理器，专注于基本功能
$modelHandler = Table::modelHandler(
    6,
    TableValueType::String,
    10, // 初始行数
    function ($h, $row, $col) use ($state) {
        $filteredData = $state->get('filteredData', []);
        $currentPage = $state->get('currentPage', 1);
        $pageSize = $state->get('pageSize', 10);
        
        $start = ($currentPage - 1) * $pageSize;
        $actualRow = $start + $row;
        
        if (!isset($filteredData[$actualRow])) {
            return Table::createValueStr('');
        }
        
        $item = $filteredData[$actualRow];
        $values = array_values($item);
        
        if ($col >= count($values)) {
            return Table::createValueStr('');
        }
        
        $value = $values[$col];
        
        switch ($col) {
            case 5: // Active column (checkbox)
                return Table::createValueInt($value ? 1 : 0);
            case 4: // Salary column - use as progress value
                $maxSalary = 150000;
                $progress = min(100, max(0, intval(($value / $maxSalary) * 100)));
                return Table::createValueInt($progress);
            default:
                return Table::createValueStr(strval($value));
        }
    },
    function ($h, $row, $col, $v) use (&$state) {
        // 处理单元格编辑
        echo "SetCellValue called: row={$row}, col={$col}\n";
        
        $filteredData = $state->get('filteredData', []);
        $currentPage = $state->get('currentPage', 1);
        $pageSize = $state->get('pageSize', 10);
        
        $start = ($currentPage - 1) * $pageSize;
        $actualRow = $start + $row;
        
        if (!isset($filteredData[$actualRow])) {
            return;
        }
        
        $type = Table::getValueType($v);
        $newValue = ($type == TableValueType::String) ? Table::valueStr($v) : Table::valueInt($v);
        
        echo "Editing row {$actualRow}, col {$col} to: '{$newValue}'\n";
        
        // 获取当前项的数据
        $currentItem = $filteredData[$actualRow];
        $allData = $state->get('allData', []);
        $itemId = $currentItem['id'];
        
        // 查找原始数据中的对应项
        foreach ($allData as $index => $item) {
            if ($item['id'] == $itemId) {
                // 获取数据项的键名
                $dataKeys = array_keys($allData[$index]);
                if (isset($dataKeys[$col])) {
                    $keyToModify = $dataKeys[$col];
                    
                    // 更新原始数据
                    $allData[$index][$keyToModify] = $newValue;
                    $state->set('allData', $allData);
                    
                    // 同时更新当前页的过滤数据
                    $filteredData[$actualRow][$keyToModify] = $newValue;
                    $state->set('filteredData', $filteredData);
                    
                    echo "Successfully updated {$keyToModify} to: '{$newValue}'\n";
                }
                break;
            }
        }
    }
);

$model = Table::createModel($modelHandler);
$modelRef = $model; // 保存模型引用

// 创建表格 - 使用正确的布尔参数
$table = Table::create($model, -1);
Table::appendTextColumn($table, "ID", 0, false); // ID列不可编辑
Table::appendTextColumn($table, "Name", 1, true); // Name列可编辑
Table::appendTextColumn($table, "Email", 2, true); // Email列可编辑
Table::appendTextColumn($table, "Department", 3, true); // Department列可编辑
Table::appendProgressBarColumn($table, "Salary", 4);
Table::appendCheckboxColumn($table, "Active", 5, 1); // Active列可编辑

// 创建搜索栏
$searchEntry = Entry::create();

$updatePageInfo = function() use ($state, &$pageInfoLabel, &$statusLabel) {
    if ($pageInfoLabel) {
        $filteredData = $state->get('filteredData', []);
        $currentPage = $state->get('currentPage', 1);
        $pageSize = $state->get('pageSize', 10);
        $totalPages = max(1, ceil(count($filteredData) / $pageSize));
        $totalCount = count($filteredData);
        
        $text = "Page {$currentPage} of {$totalPages} (Total: {$totalCount} records)";
        Label::setText($pageInfoLabel, $text);
    }
    
    // 更新状态显示
    if ($statusLabel && isset($filteredData[0])) {
        $firstRow = $filteredData[0];
        $statusText = "First Row: {$firstRow['name']} - {$firstRow['email']}";
        Label::setText($statusLabel, $statusText);
    }
};

// 搜索栏的文本改变事件
Entry::onChanged($searchEntry, function($entry) use ($state, $modelRef, $updatePageInfo, &$statusLabel) {
    $text = Entry::text($entry);
    $state->set('filterText', $text);
    doSearch($state);
    $updatePageInfo();
    refreshTable($modelRef, $state);
});

$searchButton = Button::create("Search");
Button::onClicked($searchButton, function() use ($state, $searchEntry, $modelRef, $updatePageInfo, &$statusLabel) {
    $text = Entry::text($searchEntry);
    $state->set('filterText', $text);
    doSearch($state);
    $updatePageInfo();
    refreshTable($modelRef, $state);
});

$clearButton = Button::create("Clear");
Button::onClicked($clearButton, function() use ($state, $searchEntry, $modelRef, $updatePageInfo, &$statusLabel) {
    Entry::setText($searchEntry, "");
    $state->set('filterText', '');
    doSearch($state);
    $updatePageInfo();
    refreshTable($modelRef, $state);
});

$searchBox = Box::newHorizontalBox();
Box::append($searchBox, Label::create("Filter:"), false);
Box::append($searchBox, $searchEntry, true);
Box::append($searchBox, $searchButton, false);
Box::append($searchBox, $clearButton, false);

// 创建排序按钮
$sortBox = Box::newHorizontalBox();
Box::append($sortBox, Label::create("Sort by Column (0-5):"), false);

$sortSpinbox = Spinbox::create(0, 5);
Box::append($sortBox, $sortSpinbox, false);

$sortButton = Button::create("Sort");
Button::onClicked($sortButton, function() use ($state, $sortSpinbox, $modelRef, $updatePageInfo, &$statusLabel) {
    $col = Spinbox::value($sortSpinbox);
    doSort($col, $state);
    $updatePageInfo();
    refreshTable($modelRef, $state);
});
Box::append($sortBox, $sortButton, false);

// 创建分页控件
$paginationBox = Box::newHorizontalBox();

$prevButton = Button::create("Previous");
Button::onClicked($prevButton, function() use ($state, $modelRef, $updatePageInfo, &$statusLabel) {
    $currentPage = $state->get('currentPage', 1);
    goToPage($currentPage - 1, $state);
    $updatePageInfo();
    refreshTable($modelRef, $state);
    echo "Previous page clicked
";
});
Box::append($paginationBox, $prevButton, false);

$pageInfoLabel = Label::create("");
$updatePageInfo(); // 初始更新
Box::append($paginationBox, $pageInfoLabel, true);

$nextButton = Button::create("Next");
Button::onClicked($nextButton, function() use ($state, $modelRef, $updatePageInfo, &$statusLabel) {
    $currentPage = $state->get('currentPage', 1);
    goToPage($currentPage + 1, $state);
    $updatePageInfo();
    refreshTable($modelRef, $state);
    echo "Next page clicked
";
});
Box::append($paginationBox, $nextButton, false);

// 添加编辑功能
$editButton = Button::create("Edit First Row");
Button::onClicked($editButton, function() use ($state, &$statusLabel, $modelRef) {
    $filteredData = $state->get('filteredData', []);
    $currentPage = $state->get('currentPage', 1);
    $pageSize = $state->get('pageSize', 10);
    
    $start = ($currentPage - 1) * $pageSize;
    
    if (isset($filteredData[$start])) {
        // 修改第一行的name字段
        $originalName = $filteredData[$start]['name'];
        $newName = $originalName . " (EDITED)";
        
        // 更新filteredData
        $filteredData[$start]['name'] = $newName;
        $state->set('filteredData', $filteredData);
        
        // 更新allData
        $allData = $state->get('allData', []);
        $itemId = $filteredData[$start]['id'];
        
        foreach ($allData as $index => $item) {
            if ($item['id'] == $itemId) {
                $allData[$index]['name'] = $newName;
                break;
            }
        }
        $state->set('allData', $allData);
        
        // 更新状态显示
        if ($statusLabel) {
            $statusText = "EDITED: {$originalName} -> {$newName}";
            Label::setText($statusLabel, $statusText);
        }
        
        echo "Successfully edited: {$originalName} -> {$newName}\n";
        
        // 刷新表格显示
        refreshTable($modelRef, $state);
    }
});

// 添加一个测试按钮来验证数据持久性
$testButton = Button::create("Test Data");
Button::onClicked($testButton, function() use ($state, &$statusLabel) {
    $allData = $state->get('allData', []);
    $filteredData = $state->get('filteredData', []);
    $currentPage = $state->get('currentPage', 1);
    
    echo "=== Data Test ===
";
    echo "All Data Count: " . count($allData) . "
";
    echo "Filtered Data Count: " . count($filteredData) . "
";
    echo "Current Page: {$currentPage}
";
    
    if (!empty($filteredData)) {
        echo "First 3 rows of filtered data:
";
        for ($i = 0; $i < min(3, count($filteredData)); $i++) {
            $item = $filteredData[$i];
            echo "Row {$i}: " . json_encode($item) . "
";
        }
    }
    echo "==================
";
});
Box::append($paginationBox, $editButton, false);
Box::append($paginationBox, $testButton, false);

// 创建一个简单的状态显示
$statusLabel = Label::create("Ready");

// 创建主布局
$mainBox = \Kingbes\Libui\Box::newVerticalBox();
\Kingbes\Libui\Box::append($mainBox, $searchBox, false);
\Kingbes\Libui\Box::append($mainBox, $sortBox, false);
\Kingbes\Libui\Box::append($mainBox, $table, true);
\Kingbes\Libui\Box::append($mainBox, $statusLabel, false); // 状态显示
\Kingbes\Libui\Box::append($mainBox, $paginationBox, false);

// 创建窗口
$window = Window::create("DataGrid with Pagination, Filter, and Sort", 900, 700, 1);

// 设置窗口内容
Window::setChild($window, $mainBox);

// 设置关闭事件
Window::onClosing($window, function($window) {
    App::quit();
    return 1;
});

// 更新过滤数据
updateFilteredData($state);
goToPage(1, $state);
Control::show($window);
// 运行应用
App::main();