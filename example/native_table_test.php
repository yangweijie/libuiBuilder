<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\Window;
use Kingbes\Libui\Control;
use Kingbes\Libui\Table;
use Kingbes\Libui\TableValueType;

App::init();

// 测试数据
$data = [
    ['Cell1', 'Cell2', 'Cell3'],
    ['Cell4', 'Cell5', 'Cell6'],
    ['Cell7', 'Cell8', 'Cell9']
];

echo "创建原生表格测试\n";

// 创建窗口
$window = Window::create("原生表格测试", 400, 300, 0);
Window::setMargined($window, true);
Window::onClosing($window, function ($window) {
    App::quit();
    return 1;
});

// 创建表格模型处理器
$modelHandler = Table::modelHandler(
    3, // 列数
    TableValueType::String, // 列类型
    3, // 行数
    function ($handler, $row, $column) use ($data) {
        echo "[Native Callback] Row: $row, Column: $column called\n";
        if (isset($data[$row][$column])) {
            $value = $data[$row][$column];
            echo "[Native Callback] Returning: '$value'\n";
            return Table::createValueStr($value);
        }
        echo "[Native Callback] Returning empty\n";
        return Table::createValueStr('');
    }
);

// 创建表格模型
$tableModel = Table::createModel($modelHandler);

// 创建表格
$table = Table::create($tableModel, -1);

// 添加列
Table::appendTextColumn($table, "列1", 0, false);
Table::appendTextColumn($table, "列2", 1, false);
Table::appendTextColumn($table, "列3", 2, false);

echo "表格创建完成，显示窗口\n";

Window::setChild($window, $table);
Control::show($window);
App::main();
