<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Components\DataGrid;
use Kingbes\Libui\View\State\StateManager;

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

// 创建窗口
$window = Builder::window()
    ->title('DataGrid with CRUD Operations')
    ->size(1000, 700)
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
                                $filterEntry = StateManager::instance()->getComponent('filterEntry');
                                $filterText = $filterEntry->getValue();
                                
                                $dataGrid = StateManager::instance()->getComponent('mainDataGrid');
                                if ($dataGrid) {
                                    $dataGrid->getComponent()->applyFilter($filterText);
                                }
                            }),
                        Builder::button()
                            ->text('Clear')
                            ->onClick(function($button) {
                                $filterEntry = StateManager::instance()->getComponent('filterEntry');
                                $filterEntry->setValue('');
                                
                                $dataGrid = StateManager::instance()->getComponent('mainDataGrid');
                                if ($dataGrid) {
                                    $dataGrid->getComponent()->applyFilter('');
                                }
                            })
                    ]),
                
                // CRUD 按钮区域
                Builder::hbox()
                    ->padded(false)
                    ->contains([
                        Builder::button()
                            ->text('New')
                            ->onClick(function($button) {
                                echo "New button clicked\n";
                                // 这里可以打开新建窗口
                            }),
                        Builder::button()
                            ->text('Edit')
                            ->onClick(function($button) {
                                echo "Edit button clicked\n";
                                // 这里可以打开编辑窗口
                            }),
                        Builder::button()
                            ->text('Delete')
                            ->onClick(function($button) {
                                echo "Delete button clicked\n";
                                // 这里可以执行删除操作
                            }),
                        Builder::button()
                            ->text('Clear Sort')
                            ->onClick(function($button) {
                                echo "Clear Sort button clicked\n";
                                $dataGrid = StateManager::instance()->getComponent('mainDataGrid');
                                if ($dataGrid) {
                                    // 重置排序状态 - 通过重新设置数据来实现
                                    $dg = $dataGrid->getComponent();
                                    $originalData = $dg->allData;  // 获取原始数据
                                    
                                    // 按ID重新排序（恢复原始顺序）
                                    usort($originalData, function($a, $b) {
                                        return $a['id'] <=> $b['id'];
                                    });
                                    
                                    // 重置所有数据
                                    $dg->allData = $originalData;
                                    $dg->filteredData = $originalData;
                                    $dg->displayData = array_slice($originalData, 0, $dg->pageSize);
                                    
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
                    ]),
                
                // 分页控件
                Builder::hbox()
                    ->padded(false)
                    ->contains([
                        Builder::button()
                            ->text('Previous')
                            ->onClick(function($button) {
                                $dataGrid = StateManager::instance()->getComponent('mainDataGrid');
                                if ($dataGrid) {
                                    $dataGrid->getComponent()->prevPage();
                                }
                            }),
                        Builder::label()
                            ->id('pageInfo')
                            ->text('Page 1 of 10'),
                        Builder::button()
                            ->text('Next')
                            ->onClick(function($button) {
                                $dataGrid = StateManager::instance()->getComponent('mainDataGrid');
                                if ($dataGrid) {
                                    $dataGrid->getComponent()->nextPage();
                                }
                            })
                    ])
            ])
    ]);

// 添加一个定时器来更新页码信息
$window->build(); // 先构建窗口以获取 DataGrid 引用

// 每秒钟更新一次页码信息
pcntl_async_signals(true);
pcntl_signal(SIGALRM, function() {
    $dataGrid = StateManager::instance()->getComponent('mainDataGrid');
    $pageInfoLabel = StateManager::instance()->getComponent('pageInfo');
    
    if ($dataGrid && $pageInfoLabel) {
        $dg = $dataGrid->getComponent();
        $pageInfoLabel->setValue("Page {$dg->getCurrentPage()} of {$dg->getTotalPages()}");
    }
    
    pcntl_alarm(1); // 每秒更新一次
});

pcntl_alarm(1); // 启动定时器

$window->show();
