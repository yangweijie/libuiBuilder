<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

$stateManager = StateManager::instance();

$contactsOrigin = [
    ['Lisa Sky', 'lisa@sky.com', '720-523-4329', 'Denver', 'CO'],
    ['Jordan Biggins', 'jordan@biggins.', '617-528-5399', 'Boston', 'MA'],
    ['Mary Glass', 'mary@glass.con', '847-589-8788', 'Elk Grove Village', 'IL'],
    ['Darren McGrath', 'darren@mcgrat', '206-539-9283', 'Seattle', 'WA'],
    ['Melody Hanheir', 'melody@hanhei', '213-493-8274', 'Los Angeles', 'CA'],
];

$stateManager->set('contacts', $contactsOrigin);
$stateManager->set('filteredContacts', $contactsOrigin);
$stateManager->set('searchKeyword', '');

// 事件处理器
$handlers = [];

$handlers['saveContact'] = function() use ($stateManager) {
    $row = [];
    $allFilled = true;
    $fieldNames = ['Name', 'Email', 'Phone', 'City', 'State'];
    foreach ($fieldNames as $field) {
        $value = $stateManager->get("form.{$field}", '');
        if (trim($value) === '') {
            $allFilled = false;
        }
        $row[] = $value;
    }

    if ($allFilled) {
        $contacts = $stateManager->get('contacts');
        $contacts[] = $row;
        $stateManager->set('contacts', $contacts);
        $stateManager->set('filteredContacts', $contacts);
        foreach ($fieldNames as $field) {
            $stateManager->set("form.{$field}", '');
        }
        echo "成功添加联系人: " . $row[0] . "\n";
    } else {
        echo "表单不完整，无法保存\n";
    }
};

$handlers['searchContacts'] = function() use ($stateManager) {
    $keyword = trim($stateManager->get('searchKeyword', ''));
    $contactsOrigin = $stateManager->get('contacts');
    $filteredContacts = [];

    if ($keyword === '') {
        $filteredContacts = $contactsOrigin;
    } else {
        foreach ($contactsOrigin as $row) {
            $found = false;
            foreach ($row as $cell) {
                if (strpos(strtolower($cell), strtolower($keyword)) !== false) {
                    $found = true;
                    break;
                }
            }
            if ($found) $filteredContacts[] = $row;
        }
    }

    $stateManager->set('filteredContacts', $filteredContacts);
    echo "搜索完成，找到 " . count($filteredContacts) . " 条记录\n";
};

// 渲染 HTML 模板为 Builder
$app = HtmlRenderer::render(__DIR__ . '/views/form_table_builder.ui.html', $handlers);

// 构建并显示
$app->build();
$app->show();

// 运行主循环
App::main();

