<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\Window;
use Kingbes\Libui\Control;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

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

// 创建状态管理器
$state = StateManager::instance();
$state->set('employees', $sampleData);

// 创建主窗口
$window = Builder::window([
    'title' => 'DataGrid with CRUD Operations',
    'width' => 1000,
    'height' => 700,
    'onClosing' => function() {
        App::quit();
        return 1;
    }
]);

// 创建 DataGrid
$dataGrid = Builder::dataGrid([
    'headers' => ['id', 'name', 'email', 'department', 'salary'],
    'data' => $sampleData,
    'pageSize' => 10,
    'options' => [
        'sortable' => true,
        'searchable' => true,
        'showCrudButtons' => true,
        'showPagination' => true,
        'multiSelect' => false,
        'columnWidths' => [0 => 38, 1 => 150, 2 => 200, 3 => 120, 4 => 100]
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
    ]
]);

// 设置事件处理器

// 新增员工处理
$dataGrid->onNew(function($dataGrid) {
    $newWindow = Builder::window([
        'title' => 'New Employee',
        'width' => 400,
        'height' => 300
    ]);

    // 创建表单
    $form = Builder::vbox();
    
    $nameEntry = Builder::entry();
    $nameEntry->setValue('');
    $emailEntry = Builder::entry();
    $emailEntry->setValue('');
    $departmentEntry = Builder::entry();
    $departmentEntry->setValue('');
    $salaryEntry = Builder::entry();
    $salaryEntry->setValue('80000');
    
    $nameBox = Builder::hbox();
    $nameLabel = Builder::label();
    $nameLabel->setValue('Name:');
    $nameBox->addChild($nameLabel);
    $nameBox->addChild($nameEntry);
    $form->addChild($nameBox);
    
    $emailBox = Builder::hbox();
    $emailLabel = Builder::label();
    $emailLabel->setValue('Email:');
    $emailBox->addChild($emailLabel);
    $emailBox->addChild($emailEntry);
    $form->addChild($emailBox);
    
    $departmentBox = Builder::hbox();
    $departmentLabel = Builder::label();
    $departmentLabel->setValue('Department:');
    $departmentBox->addChild($departmentLabel);
    $departmentBox->addChild($departmentEntry);
    $form->addChild($departmentBox);
    
    $salaryBox = Builder::hbox();
    $salaryLabel = Builder::label();
    $salaryLabel->setValue('Salary:');
    $salaryBox->addChild($salaryLabel);
    $salaryBox->addChild($salaryEntry);
    $form->addChild($salaryBox);

    // 按钮
    $buttonBox = Builder::hbox();
    $saveBtn = Builder::button();
    $saveBtn->text('确定');
    $cancelBtn = Builder::button();
    $cancelBtn->text('取消');
    
    // 绑定事件在构建之前
    $saveBtn->onClick(function() use ($dataGrid, $nameEntry, $emailEntry, $departmentEntry, $salaryEntry, &$windowHandle) {
        $name = $nameEntry->getValue();
        $email = $emailEntry->getValue();
        $department = $departmentEntry->getValue();
        $salary = intval($salaryEntry->getValue());
        
        if (empty($name) || empty($email)) {
            echo "Name and Email are required\n";
            return;
        }

        // 获取当前数据并计算新ID
        $currentData = $dataGrid->getAllData();
        $maxId = 0;
        foreach ($currentData as $item) {
            if ($item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }

        $newEmployee = [
            'id' => $maxId + 1,
            'name' => $name,
            'email' => $email,
            'department' => $department,
            'salary' => $salary,
        ];

        $dataGrid->addData($newEmployee);
        echo "Added new employee: {$name}\n";
        
        Control::destroy($windowHandle);
    });

    $cancelBtn->onClick(function() use (&$windowHandle) {
        Control::destroy($windowHandle);
    });
    
    $buttonBox->addChild($saveBtn);
    $buttonBox->addChild($cancelBtn);
    $form->addChild($buttonBox);

    $newWindow->addChild($form);
    $windowHandle = $newWindow->build();

    // 设置窗口关闭事件
    $newWindow->onClosing(function($w) use ($windowHandle) {
        Control::destroy($windowHandle);
        return 1;
    });

    Control::show($windowHandle);
});

// 编辑员工处理
$dataGrid->onEdit(function($dataGrid, $selectedData, $rowIndex) {
    if (!$selectedData) {
        echo "No employee selected for edit\n";
        return;
    }

    // 创建编辑窗口
    $editWindow = Builder::window([
        'title' => 'Edit Employee',
        'width' => 400,
        'height' => 300
    ]);

    // 创建表单
    $form = Builder::vbox();
    
    $idLabel = Builder::label();
    $idLabel->setValue(strval($selectedData['id']));
    $nameEntry = Builder::entry();
    $nameEntry->setValue($selectedData['name']);
    $emailEntry = Builder::entry();
    $emailEntry->setValue($selectedData['email']);
    $departmentEntry = Builder::entry();
    $departmentEntry->setValue($selectedData['department']);
    $salaryEntry = Builder::entry();
    $salaryEntry->setValue(strval($selectedData['salary']));

    $idBox = Builder::hbox();
    $idLabel = Builder::label();
    $idLabel->setValue('ID (readonly):');
    $idValueLabel = Builder::label();
    $idValueLabel->setValue(strval($selectedData['id']));
    $idBox->addChild($idLabel);
    $idBox->addChild($idValueLabel);
    $form->addChild($idBox);
    
    $nameBox = Builder::hbox();
    $nameLabel = Builder::label();
    $nameLabel->setValue('Name:');
    $nameBox->addChild($nameLabel);
    $nameBox->addChild($nameEntry);
    $form->addChild($nameBox);
    
    $emailBox = Builder::hbox();
    $emailLabel = Builder::label();
    $emailLabel->setValue('Email:');
    $emailBox->addChild($emailLabel);
    $emailBox->addChild($emailEntry);
    $form->addChild($emailBox);
    
    $departmentBox = Builder::hbox();
    $departmentLabel = Builder::label();
    $departmentLabel->setValue('Department:');
    $departmentBox->addChild($departmentLabel);
    $departmentBox->addChild($departmentEntry);
    $form->addChild($departmentBox);
    
    $salaryBox = Builder::hbox();
    $salaryLabel = Builder::label();
    $salaryLabel->setValue('Salary:');
    $salaryBox->addChild($salaryLabel);
    $salaryBox->addChild($salaryEntry);
    $form->addChild($salaryBox);

    // 按钮
    $buttonBox = Builder::hbox();
    $saveBtn = Builder::button();
    $saveBtn->text('确定');
    $cancelBtn = Builder::button();
    $cancelBtn->text('取消');
    
    // 绑定事件在构建之前
    $saveBtn->onClick(function() use ($dataGrid, $selectedData, $nameEntry, $emailEntry, $departmentEntry, $salaryEntry, &$editWindowHandle) {
        $name = $nameEntry->getValue();
        $email = $emailEntry->getValue();
        $department = $departmentEntry->getValue();
        $salary = intval($salaryEntry->getValue());
        
        if (empty($name) || empty($email)) {
            echo "Name and Email are required\n";
            return;
        }

        $updatedData = [
            'name' => $name,
            'email' => $email,
            'department' => $department,
            'salary' => $salary,
        ];

        $dataGrid->updateItem($selectedData['id'], $updatedData);
        echo "Updated employee: {$name}\n";
        
        Control::destroy($editWindowHandle);
    });

    $cancelBtn->onClick(function() use (&$editWindowHandle) {
        Control::destroy($editWindowHandle);
    });
    
    $buttonBox->addChild($saveBtn);
    $buttonBox->addChild($cancelBtn);
    $form->addChild($buttonBox);

    $editWindow->addChild($form);
    $editWindowHandle = $editWindow->build();

    // 设置窗口关闭事件
    $editWindow->onClosing(function($w) use ($editWindowHandle) {
        Control::destroy($editWindowHandle);
        return 1;
    });

    Control::show($editWindowHandle);
});

// 删除员工处理
$dataGrid->onDelete(function($dataGrid, $selectedData, $rowIndex) {
    if (!$selectedData) {
        echo "No employee selected for delete\n";
        return;
    }

    $dataGrid->deleteItem($selectedData['id']);
    echo "Deleted employee with ID: {$selectedData['id']}\n";
});

// 监听其他事件
$dataGrid->on('rowSelected', function($grid, $row, $data) {
    echo "Row {$row} selected: " . ($data['name'] ?? 'Unknown') . "\n";
});

$dataGrid->on('search', function($grid, $filterText, $count) {
    echo "Search '{$filterText}' found {$count} records\n";
});

$dataGrid->on('headerClicked', function($grid, $column, $direction) {
    echo "Sorted by column {$column} ({$direction})\n";
});

$dataGrid->on('pageChanged', function($grid, $page) {
    echo "Changed to page {$page}\n";
});

// 将 DataGrid 添加到窗口
$window->addChild($dataGrid);

// 显示窗口并运行应用
$window->build();
$window->showBuilt();