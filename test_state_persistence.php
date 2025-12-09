<?php

require_once __DIR__ . '/vendor/autoload.php';

use Kingbes\Libui\View\State\StateManager;

// 测试StateManager数据持久性
$state = StateManager::instance();

// 创建测试数据
$testData = [
    ['id' => 1, 'name' => 'Test 1', 'value' => 100],
    ['id' => 2, 'name' => 'Test 2', 'value' => 200],
];

// 保存到StateManager
$state->set('testData', $testData);

// 读取并显示
$data = $state->get('testData', []);
echo "Initial data:\n";
foreach ($data as $item) {
    echo "ID: {$item['id']}, Name: {$item['name']}, Value: {$item['value']}\n";
}

// 修改数据
$data[0]['name'] = 'Modified Test 1';
$data[0]['value'] = 999;

// 保存修改
$state->set('testData', $data);

// 再次读取验证
$modifiedData = $state->get('testData', []);
echo "\nModified data:\n";
foreach ($modifiedData as $item) {
    echo "ID: {$item['id']}, Name: {$item['name']}, Value: {$item['value']}\n";
}

echo "\nStateManager persistence test completed successfully!\n";
