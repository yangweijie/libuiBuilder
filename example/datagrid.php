<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\SortIndicator;
use Kingbes\Libui\Window;
use Kingbes\Libui\Control;
use Kingbes\Libui\Box;
use Kingbes\Libui\Button;
use Kingbes\Libui\Entry;
use Kingbes\Libui\Label;
use Kingbes\Libui\Table;
use Kingbes\Libui\TableValueType;


// 初始化应用
App::init();

// 模拟数据
$sampleData = [];
for ($i = 1; $i <= 100; $i++) {
    $sampleData[] = [
        'id' => $i,
        'name' => "Employee " . chr(65 + ($i % 26)),
        'email' => "emp{$i}@company.com",
        'department' => ['Engineering', 'Sales', 'Marketing', 'HR'][($i - 1) % 4],
        'salary' => rand(50000, 150000),
    ];
}

// 使用原生PHP变量存储状态，因为底层API不能直接使用StateManager
$allData = $sampleData;
$filteredData = $sampleData;
$currentPage = 1;
$pageSize = 10;
$sortColumn = -1; // -1表示没有排序
$sortOrder = 'none'; // 'asc', 'desc', 'none'
$selectedRow = -1; // -1表示没有选中行

// 排序辅助函数
function sortData(&$data, $column, $order) {
    if ($column < 0 || $order === 'none') {
        return; // 没有排序要求
    }

    $columnNames = ['id', 'name', 'email', 'department', 'salary'];
    if (!isset($columnNames[$column])) {
        return; // 列索引无效
    }

    $sortKey = $columnNames[$column];

    usort($data, function($a, $b) use ($sortKey, $order) {
        $valA = $a[$sortKey];
        $valB = $b[$sortKey];

        if (is_numeric($valA) && is_numeric($valB)) {
            $result = $valA <=> $valB;
        } else {
            $result = strcasecmp($valA, $valB);
        }

        return $order === 'asc' ? $result : -$result;
    });
}

// 清空所有列的排序指示器
function clearAllSortIndicators($table, $numColumns = 5) {
    for ($i = 0; $i < $numColumns; $i++) {
        Table::setHeaderSortIndicator($table, $i, SortIndicator::None);
    }
}

// 创建模型处理器
$modelHandler = Table::modelHandler(
    5, // 5列
    TableValueType::String,
    $pageSize, // 固定为页面大小，表格始终显示这么多行
    function ($h, $row, $col) use (&$filteredData, &$currentPage, $pageSize) {
        $start = ($currentPage - 1) * $pageSize;
        $actualRow = $start + $row;

        if (!isset($filteredData[$actualRow])) {
            return Table::createValueStr('');
        }

        $currentItem = $filteredData[$actualRow];
        $values = array_values($currentItem); // 转换为索引数组

        if (!isset($values[$col])) {
            return Table::createValueStr('');
        }
        return Table::createValueStr(strval($values[$col]));
    },
    null // 暂时不需要SetCellValue
);

// 创建表格模型和表格
$model = Table::createModel($modelHandler);
$table = Table::create($model, -1);

// 添加列
Table::appendTextColumn($table, "ID", 0, false);
Table::setColumnWidth($table, 0, 38);
Table::appendTextColumn($table, "Name", 1, false);
Table::appendTextColumn($table, "Email", 2, false);
Table::appendTextColumn($table, "Department", 3, false);
Table::appendTextColumn($table, "Salary", 4, false);

// 设置行选择事件
Table::onRowClicked($table, function($t, $row) use (&$selectedRow) {
    $selectedRow = $row;
    echo "Row {$row} selected\n";
});

// 创建窗口
$window = Window::create("DataGrid with CRUD Operations", 1000, 700, 0);
Window::setMargined($window, true);

Window::onClosing($window, function ($window) {
    App::quit();
    return 1;
});

// 创建控件
$filterEntry = Entry::create();
$searchBtn = Button::create("Search");
$clearBtn = Button::create("Clear");

$newBtn = Button::create("New");
$editBtn = Button::create("Edit");
$deleteBtn = Button::create("Delete");
$clearSortBtn = Button::create("Clear Sort");

// 默认禁用编辑和删除按钮
// 注意：实际禁用功能需要特定API，这里通过逻辑控制

$prevBtn = Button::create("Previous");
$nextBtn = Button::create("Next");
$pageLabel = Label::create("Page 1 of " . ceil(count($filteredData) / $pageSize));

// 创建布局
$mainBox = Box::newVerticalBox();
$filterBox = Box::newHorizontalBox();
$buttonBox = Box::newHorizontalBox();
$paginationBox = Box::newHorizontalBox();

// 过滤布局
Box::append($filterBox, Label::create("Filter:"), false);
Box::append($filterBox, $filterEntry, true);
Box::append($filterBox, $searchBtn, false);
Box::append($filterBox, $clearBtn, false);

// 按钮布局
Box::append($buttonBox, $newBtn, false);
Box::append($buttonBox, $editBtn, false);
Box::append($buttonBox, $deleteBtn, false);
Box::append($buttonBox, $clearSortBtn, false);

// 分页布局
Box::append($paginationBox, $prevBtn, false);
Box::append($paginationBox, $pageLabel, false);
Box::append($paginationBox, $nextBtn, false);

// 主布局
Box::append($mainBox, $filterBox, false);
Box::append($mainBox, $buttonBox, false);
Box::append($mainBox, $table, true);
Box::append($mainBox, $paginationBox, false);

Window::setChild($window, $mainBox);

// 事件处理函数
$refreshTable = function() use ($model, &$filteredData, &$currentPage, $pageSize, $pageLabel) {
    // 通知当前页的每一行都已更改
    $start = ($currentPage - 1) * $pageSize;
    $currentPageData = array_slice($filteredData, $start, $pageSize);

    // 通知当前页的每一行都已更改
    $rowCount = count($currentPageData);
    for ($i = 0; $i < $pageSize; $i++) {
        // 不管该行是否有数据，都通知更改，让CellValue回调返回适当的值
        Table::modelRowChanged($model, $i);
    }

    // 更新页面信息标签
    $totalPages = ceil(count($filteredData) / $pageSize);
    if ($pageLabel) {
        Label::setText($pageLabel, "Page {$currentPage} of {$totalPages}");
    }

    echo "Table refreshed. Current page: {$currentPage}, Total pages: {$totalPages}, Rows in current page: {$rowCount}\n";
};

// 设置表头点击事件
Table::onHeaderClicked($table, function($t, $column) use (&$allData, &$filteredData, &$sortColumn, &$sortOrder, &$currentPage, $pageSize, $model, $refreshTable) {
    echo "Header clicked: column {$column}\n";

    // 获取当前列的排序状态
    $currentIndicator = Table::headerSortIndicator($t, $column);
    echo "Current sort indicator: " . $currentIndicator->value . "\n";

    // 清空所有列的排序指示器
    clearAllSortIndicators($t);

    // 确定新的排序顺序
    if ($currentIndicator === SortIndicator::Ascending) {
        // 如果当前是升序，则改为降序
        $newOrder = 'desc';
        Table::setHeaderSortIndicator($t, $column, SortIndicator::Descending);
        echo "Setting column {$column} to descending\n";
    } else {
        // 如果当前是降序或无排序，则改为升序
        $newOrder = 'asc';
        Table::setHeaderSortIndicator($t, $column, SortIndicator::Ascending);
        echo "Setting column {$column} to ascending\n";
    }

    // 更新排序状态
    $sortColumn = $column;
    $sortOrder = $newOrder;

    // 对 allData 和 filteredData 进行排序
    sortData($allData, $column, $newOrder);
    sortData($filteredData, $column, $newOrder);

    // 重置到第一页并刷新表格
    $currentPage = 1;
    $refreshTable();

    echo "Sorting applied: column={$column}, order={$newOrder}\n";
});

// 清空排序按钮事件
Button::onClicked($clearSortBtn, function() use ($table, &$allData, &$filteredData, &$sortColumn, &$sortOrder, &$currentPage, $pageSize, $model, $refreshTable) {
    echo "Clear sort button clicked\n";

    // 重置排序状态
    $sortColumn = -1;
    $sortOrder = 'none';

    // 清空所有列的排序指示器
    clearAllSortIndicators($table);

    // 恢复原始数据顺序（按ID排序）
    usort($allData, function($a, $b) {
        return $a['id'] <=> $b['id'];
    });

    // 同样恢复filteredData的顺序（如果存在过滤，需要保持过滤结果但恢复内部顺序）
    if (count($allData) === count($filteredData)) {
        // 如果没有过滤，直接使用allData的顺序
        $filteredData = $allData;
    } else {
        // 如果有过滤，需要根据allData的原始顺序重新排序filteredData
        $filteredIds = [];
        foreach ($filteredData as $item) {
            $filteredIds[$item['id']] = $item;
        }

        $newFilteredData = [];
        foreach ($allData as $item) {
            if (isset($filteredIds[$item['id']])) {
                $newFilteredData[] = $item;
            }
        }
        $filteredData = $newFilteredData;
    }

    // 重置到第一页并刷新表格
    $currentPage = 1;
    $refreshTable();

    echo "Sort cleared. Data restored to original order.\n";
});

// New按钮事件
Button::onClicked($newBtn, function() use (&$allData, &$filteredData, &$currentPage, $pageSize, $sortColumn, $sortOrder, $model, $refreshTable) {
    $newId = count($allData) + 1;

    // 创建新记录窗口
    $newWindow = Window::create("New Employee", 400, 300, 0);
    Window::setMargined($newWindow, true);

    // 创建表单控件
    $nameEntry = Entry::create();
    $emailEntry = Entry::create();
    $departmentEntry = Entry::create();
    $salaryEntry = Entry::create();

    Entry::setText($nameEntry, '');
    Entry::setText($emailEntry, '');
    Entry::setText($departmentEntry, '');
    Entry::setText($salaryEntry, '80000');

    // 创建表单布局
    $formBox = Box::newVerticalBox();
    $nameBox = Box::newHorizontalBox();
    $emailBox = Box::newHorizontalBox();
    $departmentBox = Box::newHorizontalBox();
    $salaryBox = Box::newHorizontalBox();
    $buttonBox = Box::newHorizontalBox();

    Box::append($nameBox, Label::create("Name:"), false);
    Box::append($nameBox, $nameEntry, true);
    Box::append($emailBox, Label::create("Email:"), false);
    Box::append($emailBox, $emailEntry, true);
    Box::append($departmentBox, Label::create("Department:"), false);
    Box::append($departmentBox, $departmentEntry, true);
    Box::append($salaryBox, Label::create("Salary:"), false);
    Box::append($salaryBox, $salaryEntry, true);
    Box::append($formBox, $nameBox, false);
    Box::append($formBox, $emailBox, false);
    Box::append($formBox, $departmentBox, false);
    Box::append($formBox, $salaryBox, false);

    // 保存按钮
    $saveBtn = Button::create("Save");
    $cancelBtn = Button::create("Cancel");
    Box::append($buttonBox, $saveBtn, false);
    Box::append($buttonBox, $cancelBtn, false);
    Box::append($formBox, $buttonBox, false);

    Window::setChild($newWindow, $formBox);

    // 保存事件
    Button::onClicked($saveBtn, function() use (&$allData, &$filteredData, $newWindow, $nameEntry, $emailEntry, $departmentEntry, $salaryEntry, &$currentPage, $pageSize, $model, $refreshTable) {
        $name = Entry::text($nameEntry);
        $email = Entry::text($emailEntry);
        $department = Entry::text($departmentEntry);
        $salary = intval(Entry::text($salaryEntry));

        // 验证输入
        if (empty($name) || empty($email)) {
            echo "Name and Email are required\n";
            return;
        }

        // 计算新ID：使用所有数据中最大ID+1
        $maxId = 0;
        foreach ($allData as $item) {
            if ($item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }
        $newId = $maxId + 1;

        $newRow = [
            'id' => $newId,
            'name' => $name,
            'email' => $email,
            'department' => $department,
            'salary' => $salary,
        ];
        $allData[] = $newRow;
        $filteredData[] = $newRow; // 也要更新过滤后的数据

        // 刷新以获取最新的总页数
        $totalPages = ceil(count($filteredData) / $pageSize);

        // 如果新增数据导致页数增加，且当前在最后一页，则跳转到新页
        if ($currentPage == $totalPages - 1 && $totalPages > ceil((count($filteredData) - 1) / $pageSize)) {
            // 原来的总页数（没有新记录时）
            $oldTotalPages = ceil((count($filteredData) - 1) / $pageSize);
            if ($currentPage == $oldTotalPages && $totalPages > $oldTotalPages) {
                $currentPage = $totalPages;  // 自动跳转到新页
            }
        }

        // 刷新表格显示，确保新数据反映在UI中
        $refreshTable();

        echo "Added new row: {$name} with ID: {$newId}\n";

        // 关闭窗口
        Control::destroy($newWindow);
    });

    // 取消事件
    Button::onClicked($cancelBtn, function() use ($newWindow) {
        Control::destroy($newWindow);
    });

    // 设置窗口关闭事件
    Window::onClosing($newWindow, function($w) {
        Control::destroy($w);
        return 1;
    });

    Control::show($newWindow);
});



// Edit按钮事件
Button::onClicked($editBtn, function() use (&$allData, &$filteredData, &$selectedRow, &$currentPage, $pageSize, $model, $refreshTable) {
    if ($selectedRow < 0) {
        echo "No row selected for edit\n";
        return;
    }

    // 计算当前显示的数据
    $start = ($currentPage - 1) * $pageSize;
    $currentRowData = $filteredData[$start + $selectedRow] ?? null;

    if (!$currentRowData) {
        echo "Selected row data not found\n";
        return;
    }

    // 创建编辑窗口
    $editWindow = Window::create("Edit Employee", 400, 300, 0);
    Window::setMargined($editWindow, true);

    // 创建表单控件并设置当前值
    $idLabel = Label::create(strval($currentRowData['id']));
    $nameEntry = Entry::create();
    $emailEntry = Entry::create();
    $departmentEntry = Entry::create();
    $salaryEntry = Entry::create();

    Entry::setText($nameEntry, $currentRowData['name']);
    Entry::setText($emailEntry, $currentRowData['email']);
    Entry::setText($departmentEntry, $currentRowData['department']);
    Entry::setText($salaryEntry, strval($currentRowData['salary']));

    // 创建表单布局
    $formBox = Box::newVerticalBox();
    $idBox = Box::newHorizontalBox();
    $nameBox = Box::newHorizontalBox();
    $emailBox = Box::newHorizontalBox();
    $departmentBox = Box::newHorizontalBox();
    $salaryBox = Box::newHorizontalBox();
    $buttonBox = Box::newHorizontalBox();

    Box::append($idBox, Label::create("ID (readonly):"), false);
    Box::append($idBox, $idLabel, true);
    Box::append($nameBox, Label::create("Name:"), false);
    Box::append($nameBox, $nameEntry, true);
    Box::append($emailBox, Label::create("Email:"), false);
    Box::append($emailBox, $emailEntry, true);
    Box::append($departmentBox, Label::create("Department:"), false);
    Box::append($departmentBox, $departmentEntry, true);
    Box::append($salaryBox, Label::create("Salary:"), false);
    Box::append($salaryBox, $salaryEntry, true);
    Box::append($formBox, $idBox, false);
    Box::append($formBox, $nameBox, false);
    Box::append($formBox, $emailBox, false);
    Box::append($formBox, $departmentBox, false);
    Box::append($formBox, $salaryBox, false);

    // 保存和取消按钮
    $saveBtn = Button::create("Save");
    $cancelBtn = Button::create("Cancel");
    Box::append($buttonBox, $saveBtn, false);
    Box::append($buttonBox, $cancelBtn, false);
    Box::append($formBox, $buttonBox, false);

    Window::setChild($editWindow, $formBox);

    // 保存事件
    Button::onClicked($saveBtn, function() use (&$allData, &$filteredData, $editWindow, $currentRowData, $nameEntry, $emailEntry, $departmentEntry, $salaryEntry, $model, &$currentPage, $pageSize, $selectedRow, $refreshTable) {
        $name = Entry::text($nameEntry);
        $email = Entry::text($emailEntry);
        $department = Entry::text($departmentEntry);
        $salary = intval(Entry::text($salaryEntry));

        // 验证输入
        if (empty($name) || empty($email)) {
            echo "Name and Email are required\n";
            return;
        }

        // 更新原始数据
        foreach ($allData as &$item) {
            if ($item['id'] == $currentRowData['id']) {
                $item['name'] = $name;
                $item['email'] = $email;
                $item['department'] = $department;
                $item['salary'] = $salary;
                break;
            }
        }

        // 更新过滤数据
        $updatedIndex = -1;
        foreach ($filteredData as $key=>&$item) {
            if ($item['id'] == $currentRowData['id']) {
                $item['name'] = $name;
                $item['email'] = $email;
                $item['department'] = $department;
                $item['salary'] = $salary;
                $updatedIndex = $key;
                break;
            }
        }

        // 刷新整个表格以确保更改显示
        $refreshTable();

        echo "Updated row: {$name}\n";

        // 关闭窗口
        Control::destroy($editWindow);
    });

    // 取消事件
    Button::onClicked($cancelBtn, function() use ($editWindow) {
        Control::destroy($editWindow);
    });

    // 设置窗口关闭事件
    Window::onClosing($editWindow, function($w) {
        Control::destroy($w);
        return 1;
    });

    Control::show($editWindow);
});

// Delete按钮事件
Button::onClicked($deleteBtn, function() use (&$allData, &$filteredData, &$selectedRow, &$currentPage, $pageSize, $model, $refreshTable) {
    if ($selectedRow < 0) {
        echo "No row selected for delete\n";
        return;
    }

    // 计算当前显示的数据
    $start = ($currentPage - 1) * $pageSize;
    $currentRowData = $filteredData[$start + $selectedRow] ?? null;

    if (!$currentRowData) {
        echo "Selected row data not found\n";
        return;
    }

    // 找到要删除的记录在filteredData中的索引
    $deleteIndex = $start + $selectedRow;

    // 从 allData 中删除记录
    $newAllData = [];
    foreach ($allData as $item) {
        if ($item['id'] != $currentRowData['id']) {
            $newAllData[] = $item;
        }
    }
    $allData = $newAllData;

    // 从 filteredData 中删除记录
    $newFilteredData = [];
    foreach ($filteredData as $item) {
        if ($item['id'] != $currentRowData['id']) {
            $newFilteredData[] = $item;
        }
    }
    $filteredData = $newFilteredData;

    $selectedRow = -1; // 清除选中行

    // 计算删除后的总页数
    $totalPagesAfterDelete = ceil(count($filteredData) / $pageSize);

    // 如果当前页超过新的总页数，则返回到最后一页
    if ($currentPage > $totalPagesAfterDelete && $totalPagesAfterDelete >= 1) {
        $currentPage = $totalPagesAfterDelete;
    } else if ($totalPagesAfterDelete == 0 && $currentPage > 1) {
        // 如果删除后没有数据了，且当前页大于1，则回到第一页
        $currentPage = 1;
    }

    // 刷新表格显示，以反映删除操作
    $refreshTable();

    echo "Deleted row with ID: {$currentRowData['id']}\n";
});

// 上一页事件
Button::onClicked($prevBtn, function() use (&$currentPage, $filteredData, $pageSize, $refreshTable) {
    $totalPages = ceil(count($filteredData) / $pageSize);
    if ($currentPage > 1) {
        $currentPage--;
        $refreshTable();
        echo "Previous page: {$currentPage}\n";
    }
});

// 下一页事件
Button::onClicked($nextBtn, function() use (&$allData, &$filteredData, &$currentPage, $pageSize, $refreshTable) {
    $totalPages = ceil(count($filteredData) / $pageSize);
    if ($currentPage < $totalPages) {
        $currentPage++;
        $refreshTable();
        echo "Next page: {$currentPage}\n";
    } else {
        echo "Cannot go to next page. Current: {$currentPage}, Total: {$totalPages}\n";
    }
});

// 搜索事件
Button::onClicked($searchBtn, function() use ($filterEntry, &$filteredData, $allData, &$currentPage, $pageSize, $refreshTable) {
    $filterText = Entry::text($filterEntry);

    if (empty($filterText)) {
        $filteredData = $allData;
    } else {
        $filterText = strtolower($filterText);
        $filteredData = array_filter($allData, function($item) use ($filterText) {
            foreach ($item as $value) {
                if (stripos((string)$value, $filterText) !== false) {
                    return true;
                }
            }
            return false;
        });
        $filteredData = array_values($filteredData);
    }

    // 重置到第一页
    $currentPage = 1;

    // 确保当前页不超过总页数
    $totalPages = ceil(count($filteredData) / $pageSize);
    if ($currentPage > $totalPages) {
        $currentPage = $totalPages; // 如果当前页超过总页数，跳转到最后一页
    }

    // 刷新表格显示
    $refreshTable();

    echo "Filter applied. Found " . count($filteredData) . " records. Total pages: {$totalPages}\n";
});

// 清除搜索事件
Button::onClicked($clearBtn, function() use ($filterEntry, &$filteredData, $allData, &$currentPage, $pageSize, $refreshTable) {
    Entry::setText($filterEntry, '');
    $filteredData = $allData;
    $currentPage = 1;

    // 刷新表格显示
    $refreshTable();

    echo "Filter cleared\n";
});

Control::show($window);
App::main();