<?php

require_once __DIR__ . '/vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;

App::init();

$testData = [
    ['A1', 'B1', 'C1'],
    ['A2', 'B2', 'C2'],
    ['A3', 'B3', 'C3'],
    ['A4', 'B4', 'C4'],
    ['A5', 'B5', 'C5']
];

$table = Builder::table()
    ->headers(['列A', '列B', '列C'])
    ->data($testData);

// 2行Grid：标题 + 表格
$window = Builder::window()
    ->title('2行Grid测试')
    ->size(400, 300)
    ->contains([
        Builder::grid()->padded(true)->contains([
            Builder::label()
                ->text('2行Grid - 标题')
                ->row(0)->col(0)->colspan(3),
            
            $table
                ->row(1)->col(0)->colspan(3)
                ->expand('both')
        ])
    ]);

echo "2行Grid测试\n";
$window->show();
App::main();
