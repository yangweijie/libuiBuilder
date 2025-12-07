<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Components\DataGrid;

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

// 创建主窗口
$window = Builder::window()
    ->title('DataGrid with Full CRUD Operations')
    ->size(1200, 800)
    ->contains([
        Builder::vbox()
            ->padded(true)
            ->contains([
                // 搜索和过滤区域
                Builder::hbox()
                    ->padded(false)
                    ->contains([
                        Builder::label()->text('Filter:'),
                        Builder::entry()
                            ->id('filterEntry')
                            ->placeholder('Enter search term'),
                        Builder::button()
                            ->text('Search')
                            ->onClick(function($button) {
                                $filterEntry = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('filterEntry');
                                $filterText = $filterEntry->getValue();
                                
                                $dataGrid = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('mainDataGrid');
                                if ($dataGrid) {
                                    $dataGrid->getComponent()->applyFilter($filterText);
                                }
                                
                                // 更新页码信息
                                $pageInfoLabel = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('pageInfo');
                                if ($pageInfoLabel && $dataGrid) {
                                    $dg = $dataGrid->getComponent();
                                    $pageInfoLabel->setValue("Page {$dg->getCurrentPage()} of {$dg->getTotalPages()} (Total: {$dg->getTotalRecords()} records)");
                                }
                            }),
                        Builder::button()
                            ->text('Clear')
                            ->onClick(function($button) {
                                $filterEntry = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('filterEntry');
                                $filterEntry->setValue('');
                                
                                $dataGrid = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('mainDataGrid');
                                if ($dataGrid) {
                                    $dataGrid->getComponent()->applyFilter('');
                                }
                                
                                // 更新页码信息
                                $pageInfoLabel = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('pageInfo');
                                if ($pageInfoLabel && $dataGrid) {
                                    $dg = $dataGrid->getComponent();
                                    $pageInfoLabel->setValue("Page {$dg->getCurrentPage()} of {$dg->getTotalPages()} (Total: {$dg->getTotalRecords()} records)");
                                }
                            })
                    ]),
                
                // CRUD 按钮区域
                Builder::hbox()
                    ->padded(false)
                    ->contains([
                        Builder::button()
                            ->text('New')
                            ->id('newBtn')
                            ->onClick(function($button) use ($sampleData) {
                                echo "New button clicked\n";
                                
                                // 创建新记录窗口
                                $newWindow = Builder::window()
                                    ->title('New Employee')
                                    ->size(400, 300)
                                    ->contains([
                                        Builder::vbox()
                                            ->padded(true)
                                            ->contains([
                                                Builder::grid()->form([
                                                    [
                                                        'label' => Builder::label()->text('Name:'),
                                                        'control' => Builder::entry()->id('newName')->placeholder('Enter name')
                                                    ],
                                                    [
                                                        'label' => Builder::label()->text('Email:'),
                                                        'control' => Builder::entry()->id('newEmail')->placeholder('Enter email')
                                                    ],
                                                    [
                                                        'label' => Builder::label()->text('Department:'),
                                                        'control' => Builder::combobox()->id('newDepartment')->items(['Engineering', 'Sales', 'Marketing', 'HR'])
                                                    ],
                                                    [
                                                        'label' => Builder::label()->text('Salary:'),
                                                        'control' => Builder::entry()->id('newSalary')->placeholder('Enter salary')->bind('newSalary')
                                                    ]
                                                ]),
                                                Builder::hbox()->contains([
                                                    Builder::button()
                                                        ->text('Save')
                                                        ->onClick(function($btn) {
                                                            $nameComp = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('newName');
                                                            $emailComp = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('newEmail');
                                                            $deptComp = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('newDepartment');
                                                            $salaryComp = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('newSalary');
                                                            
                                                            $name = $nameComp ? $nameComp->getValue() : '';
                                                            $email = $emailComp ? $emailComp->getValue() : '';
                                                            $department = $deptComp ? $deptComp->getValue()['item'] : '';
                                                            $salary = $salaryComp ? intval($salaryComp) : 80000;
                                                            
                                                            // 验证输入
                                                            if (empty($name) || empty($email)) {
                                                                echo "Name and Email are required\n";
                                                                return;
                                                            }
                                                            
                                                            // 获取主数据网格
                                                            $dataGrid = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('mainDataGrid');
                                                            if ($dataGrid) {
                                                                // 计算新ID
                                                                $maxId = 0;
                                                                foreach ($dataGrid->getComponent()->allData as $item) {
                                                                    if ($item['id'] > $maxId) {
                                                                        $maxId = $item['id'];
                                                                    }
                                                                }
                                                                $newId = $maxId + 1;
                                                                
                                                                $newRecord = [
                                                                    'id' => $newId,
                                                                    'name' => $name,
                                                                    'email' => $email,
                                                                    'department' => $department,
                                                                    'salary' => $salary,
                                                                ];
                                                                
                                                                $dataGrid->getComponent()->addRecord($newRecord);
                                                                
                                                                // 更新页码信息
                                                                $pageInfoLabel = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('pageInfo');
                                                                if ($pageInfoLabel) {
                                                                    $dg = $dataGrid->getComponent();
                                                                    $pageInfoLabel->setValue("Page {$dg->getCurrentPage()} of {$dg->getTotalPages()} (Total: {$dg->getTotalRecords()} records)");
                                                                }
                                                                
                                                                echo "Added new record: $name with ID: $newId\n";
                                                            }
                                                            
                                                            // 关闭新记录窗口
                                                            $newWin = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('newEmployeeWindow');
                                                            if ($newWin) {
                                                                $newWin->getComponent()->close();
                                                            }
                                                        }),
                                                    Builder::button()
                                                        ->text('Cancel')
                                                        ->onClick(function($btn) {
                                                            $newWin = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('newEmployeeWindow');
                                                            if ($newWin) {
                                                                $newWin->getComponent()->close();
                                                            }
                                                        })
                                                ])
                                            ])
                                    ]);
                                
                                $newWindow->show();
                            }),
                        Builder::button()
                            ->text('Edit')
                            ->id('editBtn')
                            ->onClick(function($button) {
                                echo "Edit button clicked\n";
                                
                                // 获取选中的行（这里简化处理，实际需要获取选中行）
                                $dataGrid = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('mainDataGrid');
                                if ($dataGrid) {
                                    // 模拟获取选中行的数据
                                    $currentPageData = $dataGrid->getComponent()->getCurrentPageData();
                                    if (!empty($currentPageData)) {
                                        $selectedRecord = $currentPageData[0]; // 简化：总是编辑第一页第一行
                                        
                                        // 创建编辑窗口
                                        $editWindow = Builder::window()
                                            ->title('Edit Employee')
                                            ->size(400, 300)
                                            ->contains([
                                                Builder::vbox()
                                                    ->padded(true)
                                                    ->contains([
                                                        Builder::label()->text("Editing ID: {$selectedRecord['id']}")->id('editId'),
                                                        Builder::grid()->form([
                                                            [
                                                                'label' => Builder::label()->text('Name:'),
                                                                'control' => Builder::entry()->id('editName')->value($selectedRecord['name'])
                                                            ],
                                                            [
                                                                'label' => Builder::label()->text('Email:'),
                                                                'control' => Builder::entry()->id('editEmail')->value($selectedRecord['email'])
                                                            ],
                                                            [
                                                                'label' => Builder::label()->text('Department:'),
                                                                'control' => Builder::combobox()->id('editDepartment')->items(['Engineering', 'Sales', 'Marketing', 'HR'])->value($selectedRecord['department'])
                                                            ],
                                                            [
                                                                'label' => Builder::label()->text('Salary:'),
                                                                'control' => Builder::entry()->id('editSalary')->value($selectedRecord['salary'])
                                                            ]
                                                        ]),
                                                        Builder::hbox()->contains([
                                                            Builder::button()
                                                                ->text('Save')
                                                                ->onClick(function($btn) use ($selectedRecord) {
                                                                    $nameComp = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('editName');
                                                                    $emailComp = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('editEmail');
                                                                    $deptComp = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('editDepartment');
                                                                    $salaryComp = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('editSalary');
                                                                    
                                                                    $name = $nameComp ? $nameComp->getValue() : '';
                                                                    $email = $emailComp ? $emailComp->getValue() : '';
                                                                    $department = $deptComp ? $deptComp->getValue()['item'] : '';
                                                                    $salary = $salaryComp ? intval($salaryComp) : 0;
                                                                    
                                                                    // 验证输入
                                                                    if (empty($name) || empty($email)) {
                                                                        echo "Name and Email are required\n";
                                                                        return;
                                                                    }
                                                                    
                                                                    // 获取主数据网格并更新记录
                                                                    $dataGrid = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('mainDataGrid');
                                                                    if ($dataGrid) {
                                                                        $updatedRecord = [
                                                                            'id' => $selectedRecord['id'],
                                                                            'name' => $name,
                                                                            'email' => $email,
                                                                            'department' => $department,
                                                                            'salary' => $salary,
                                                                        ];
                                                                        
                                                                        // 找到原始数据中的索引并更新
                                                                        $allData = &$dataGrid->getComponent()->allData;
                                                                        foreach ($allData as $index => $item) {
                                                                            if ($item['id'] == $selectedRecord['id']) {
                                                                                $allData[$index] = $updatedRecord;
                                                                                break;
                                                                            }
                                                                        }
                                                                        
                                                                        // 更新过滤数据
                                                                        $filteredData = &$dataGrid->getComponent()->filteredData;
                                                                        foreach ($filteredData as $index => $item) {
                                                                            if ($item['id'] == $selectedRecord['id']) {
                                                                                $filteredData[$index] = $updatedRecord;
                                                                                break;
                                                                            }
                                                                        }
                                                                        
                                                                        $dataGrid->getComponent()->refreshTable();
                                                                        echo "Updated record ID: {$selectedRecord['id']}\n";
                                                                    }
                                                                    
                                                                    // 关闭编辑窗口
                                                                    $editWin = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('editEmployeeWindow');
                                                                    if ($editWin) {
                                                                        $editWin->getComponent()->close();
                                                                    }
                                                                }),
                                                            Builder::button()
                                                                ->text('Cancel')
                                                                ->onClick(function($btn) {
                                                                    $editWin = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('editEmployeeWindow');
                                                                    if ($editWin) {
                                                                        $editWin->getComponent()->close();
                                                                    }
                                                                })
                                                        ])
                                                    ])
                                            ]);
                                        
                                        $editWindow->show();
                                    }
                                }
                            }),
                        Builder::button()
                            ->text('Delete')
                            ->id('deleteBtn')
                            ->onClick(function($button) {
                                echo "Delete button clicked\n";
                                
                                // 获取选中的行
                                $dataGrid = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('mainDataGrid');
                                if ($dataGrid) {
                                    $currentPageData = $dataGrid->getComponent()->getCurrentPageData();
                                    if (!empty($currentPageData)) {
                                        $selectedRecord = $currentPageData[0]; // 简化：总是删除第一页第一行
                                        
                                        // 从 allData 中删除
                                        $allData = &$dataGrid->getComponent()->allData;
                                        foreach ($allData as $index => $item) {
                                            if ($item['id'] == $selectedRecord['id']) {
                                                unset($allData[$index]);
                                                break;
                                            }
                                        }
                                        $allData = array_values($allData); // 重新索引
                                        
                                        // 从 filteredData 中删除
                                        $filteredData = &$dataGrid->getComponent()->filteredData;
                                        foreach ($filteredData as $index => $item) {
                                            if ($item['id'] == $selectedRecord['id']) {
                                                unset($filteredData[$index]);
                                                break;
                                            }
                                        }
                                        $filteredData = array_values($filteredData); // 重新索引
                                        
                                        $dataGrid->getComponent()->updatePagination();
                                        $dataGrid->getComponent()->updatePageData();
                                        $dataGrid->getComponent()->refreshTable();
                                        
                                        // 更新页码信息
                                        $pageInfoLabel = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('pageInfo');
                                        if ($pageInfoLabel) {
                                            $dg = $dataGrid->getComponent();
                                            $pageInfoLabel->setValue("Page {$dg->getCurrentPage()} of {$dg->getTotalPages()} (Total: {$dg->getTotalRecords()} records)");
                                        }
                                        
                                        echo "Deleted record ID: {$selectedRecord['id']}\n";
                                    }
                                }
                            }),
                        Builder::button()
                            ->text('Clear Sort')
                            ->onClick(function($button) {
                                echo "Clear Sort button clicked\n";
                                $dataGrid = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('mainDataGrid');
                                if ($dataGrid) {
                                    // 重置为按ID排序
                                    $dg = $dataGrid->getComponent();
                                    usort($dg->allData, function($a, $b) {
                                        return $a['id'] <=> $b['id'];
                                    });
                                    
                                    // 如果没有过滤，filteredData 就是 allData
                                    if (count($dg->allData) == count($dg->filteredData)) {
                                        $dg->filteredData = $dg->allData;
                                    } else {
                                        // 如果有滤，需要保持过滤结果但重排顺序
                                        $filteredIds = [];
                                        foreach ($dg->filteredData as $item) {
                                            $filteredIds[$item['id']] = $item;
                                        }
                                        
                                        $newFilteredData = [];
                                        foreach ($dg->allData as $item) {
                                            if (isset($filteredIds[$item['id']])) {
                                                $newFilteredData[] = $item;
                                            }
                                        }
                                        $dg->filteredData = $newFilteredData;
                                    }
                                    
                                    $dg->updatePageData();
                                    $dg->refreshTable();
                                }
                            })
                    ]),
                
                // 数据表格
                (new DataGrid())
                    ->id('mainDataGrid')
                    ->headers(['ID', 'Name', 'Email', 'Department', 'Salary'])
                    ->data($sampleData)
                    ->options([
                        'sortable' => true,
                        'multiSelect' => false,
                        'headerVisible' => true,
                        'pageSize' => 10,
                        'columnWidths' => [50, 150, 200, 120, 100]
                    ])
                    ->onEvent('onHeaderClicked', function($table, $column, $sortColumn, $sortDirection) {
                        echo "Header clicked: $column. Now sorted by column $sortColumn ($sortDirection)\n";
                    })
                    ->onEvent('onRowClicked', function($table, $row) {
                        echo "Row clicked: $row\n";
                    }),
                
                // 分页控件
                Builder::hbox()
                    ->padded(false)
                    ->contains([
                        Builder::button()
                            ->text('Previous')
                            ->onClick(function($button) {
                                $dataGrid = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('mainDataGrid');
                                if ($dataGrid) {
                                    $dataGrid->getComponent()->prevPage();
                                    
                                    // 更新页码信息
                                    $pageInfoLabel = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('pageInfo');
                                    if ($pageInfoLabel) {
                                        $dg = $dataGrid->getComponent();
                                        $pageInfoLabel->setValue("Page {$dg->getCurrentPage()} of {$dg->getTotalPages()} (Total: {$dg->getTotalRecords()} records)");
                                    }
                                }
                            }),
                        Builder::label()
                            ->id('pageInfo')
                            ->text('Page 1 of 10 (Total: 100 records)'),
                        Builder::button()
                            ->text('Next')
                            ->onClick(function($button) {
                                $dataGrid = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('mainDataGrid');
                                if ($dataGrid) {
                                    $dataGrid->getComponent()->nextPage();
                                    
                                    // 更新页码信息
                                    $pageInfoLabel = \Kingbes\Libui\View\State\StateManager::instance()->getComponent('pageInfo');
                                    if ($pageInfoLabel) {
                                        $dg = $dataGrid->getComponent();
                                        $pageInfoLabel->setValue("Page {$dg->getCurrentPage()} of {$dg->getTotalPages()} (Total: {$dg->getTotalRecords()} records)");
                                    }
                                }
                            })
                    ])
            ])
    ]);

$window->show();
App::run();