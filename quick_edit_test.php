<?php

require_once __DIR__ . '/vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\Window;
use Kingbes\Libui\Control;
use Kingbes\Libui\Table;
use Kingbes\Libui\TableValueType;

App::init();

$window = Window::create("Edit Test", 400, 300, 0);
Window::setMargined($window, true);

Window::onClosing($window, function ($window) {
    App::quit();
    return 1;
});

$data = [
    ['name' => 'Test 1', 'value' => '100'],
    ['name' => 'Test 2', 'value' => '200'],
];

$modelHandler = Table::modelHandler(
    2,
    TableValueType::String,
    2,
    function ($handler, $row, $column) use (&$data) {
        if ($column == 0) {
            return Table::createValueStr($data[$row]['name']);
        } else {
            return Table::createValueStr($data[$row]['value']);
        }
    },
    function ($handler, $row, $column, $v) use (&$data) {
        echo "SetCellValue CALLED! row={$row}, column={$column}\n";
        var_dump($v);
        
        if ($column == 0) {
            $data[$row]['name'] = Table::valueStr($v);
            echo "Updated name to: {$data[$row]['name']}\n";
        } else {
            $data[$row]['value'] = Table::valueStr($v);
            echo "Updated value to: {$data[$row]['value']}\n";
        }
        
        echo "Current data: " . json_encode($data[$row]) . "\n";
    }
);

$tableModel = Table::createModel($modelHandler);
$table = Table::create($tableModel, -1);

Table::appendTextColumn($table, "Name", 0, true);
Table::appendTextColumn($table, "Value", 1, true);

Window::setChild($window, $table);
Control::show($window);
App::main();