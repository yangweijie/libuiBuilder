<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;

App::init();

// 简单的表格示例
$data = [
    ['ID' => '1', 'Name' => 'John Doe', 'Position' => 'Developer'],
    ['ID' => '2', 'Name' => 'Jane Smith', 'Position' => 'Designer'],
    ['ID' => '3', 'Name' => 'Bob Johnson', 'Position' => 'Manager']
];

$window = Builder::window()
    ->title('Simple Table Example')
    ->size(600, 400)
    ->contains([
        Builder::table()
            ->headers(['ID', 'Name', 'Position'])
            ->data($data)
            ->options([
                'sortable' => true,
                'multiSelect' => false,
                'headerVisible' => true
            ])
            ->onEvent('onRowClicked', function($table, $row) use ($data) {
                echo "Row $row clicked: " . $data[$row]['Name'] . "\n";
            })
    ]);

$window->show();
