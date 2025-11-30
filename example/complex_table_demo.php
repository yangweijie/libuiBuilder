<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

$sm = StateManager::instance();

// 初始完整数据（二维数值数组）
$all = [
    [1,'Alice A','alice@example.com','111-111-1111','CityA','CA'],
    [2,'Bob B','bob@example.com','222-222-2222','CityB','NY'],
    [3,'Carol C','carol@example.com','333-333-3333','CityC','TX'],
    [4,'Dave D','dave@example.com','444-444-4444','CityD','WA'],
    [5,'Eve E','eve@example.com','555-555-5555','CityE','FL'],
    [6,'Frank F','frank@example.com','666-666-6666','CityF','CA'],
    [7,'Grace G','grace@example.com','777-777-7777','CityG','OR'],
    [8,'Heidi H','heidi@example.com','888-888-8888','CityH','NV'],
    [9,'Ivan I','ivan@example.com','999-999-9999','CityI','WA'],
    [10,'Judy J','judy@example.com','101-010-1010','CityJ','NY'],
];

$sm->set('allData', $all);
$sm->set('filtered', $all);
$sm->set('pagedData', $all);
$sm->set('selectedRows', []); // 存放选中行的 id
$sm->set('searchKeyword', '');
$sm->set('page', 1);
$sm->set('pageSize', 5);
$sm->set('pageInfo', 'Page 1');
$sm->set('showForm', false);
$sm->set('formTitle','Add Contact');
$sm->set('form', ['name'=>'','email'=>'','phone'=>'','city'=>'','state'=>'']);

// 辅助：重新计算分页
function recomputePaging(StateManager $sm) {
    $all = $sm->get('filtered', []);
    $page = max(1, (int)$sm->get('page',1));
    $pageSize = max(1, (int)$sm->get('pageSize',5));
    $total = count($all);
    $totalPages = max(1, (int)ceil($total / $pageSize));
    if ($page > $totalPages) $page = $totalPages;
    $start = ($page-1)*$pageSize;
    $slice = array_slice($all, $start, $pageSize);
    $sm->set('pagedData', $slice);
    $sm->set('page', $page);
    $sm->set('pageInfo', "Page {$page} / {$totalPages}");
}

// 事件处理器
$handlers = [];

$handlers['search'] = function() use ($sm) {
    $kw = trim($sm->get('searchKeyword',''));
    $all = $sm->get('allData', []);
    if ($kw === '') {
        $filtered = $all;
    } else {
        $filtered = [];
        foreach ($all as $row) {
            foreach ($row as $cell) {
                if (strpos(strtolower($cell), strtolower($kw)) !== false) {
                    $filtered[] = $row; break;
                }
            }
        }
    }
    $sm->set('filtered', $filtered);
    $sm->set('page',1);
    recomputePaging($sm);
    echo "搜索完成, 找到 " . count($filtered) . " 条\n";
};

$handlers['clearSearch'] = function() use ($sm) {
    $sm->set('searchKeyword','');
    $sm->set('filtered', $sm->get('allData', []));
    $sm->set('page',1);
    recomputePaging($sm);
    echo "清除搜索\n";
};

$handlers['openAdd'] = function() use ($sm) {
    $sm->set('showForm', true);
    $sm->set('formTitle','Add Contact');
    $sm->set('form', ['name'=>'','email'=>'','phone'=>'','city'=>'','state'=>'']);
};

$handlers['openEdit'] = function() use ($sm) {
    $selected = $sm->get('selectedRows', []);
    if (empty($selected)) { echo "请先选择一行用于编辑\n"; return; }
    $id = $selected[0];
    $all = $sm->get('allData', []);
    foreach ($all as $row) {
        if ($row[0] == $id) {
            $sm->set('formTitle','Edit Contact');
            $sm->set('form',['name'=>$row[1],'email'=>$row[2],'phone'=>$row[3],'city'=>$row[4],'state'=>$row[5],'id'=>$id]);
            $sm->set('showForm', true);
            return;
        }
    }
    echo "未找到要编辑的行\n";
};

$handlers['save'] = function() use ($sm) {
    $form = $sm->get('form');
    $all = $sm->get('allData', []);
    if (!empty($form['id'])) {
        // 编辑
        foreach ($all as &$row) {
            if ($row[0] == $form['id']) {
                $row[1] = $form['name']; $row[2] = $form['email']; $row[3] = $form['phone']; $row[4] = $form['city']; $row[5] = $form['state'];
                break;
            }
        }
        $sm->set('allData', $all);
        echo "保存编辑: {$form['name']}\n";
    } else {
        // 新增
        $next = 1;
        if (!empty($all)) {
            $ids = array_column($all,0);
            $next = max($ids)+1;
        }
        $all[] = [$next, $form['name'], $form['email'], $form['phone'], $form['city'], $form['state']];
        $sm->set('allData', $all);
        echo "新增: {$form['name']}\n";
    }
    // 关闭表单并重新过滤/分页
    $sm->set('showForm', false);
    $sm->set('filtered', $sm->get('allData'));
    recomputePaging($sm);
};

$handlers['cancel'] = function() use ($sm) {
    $sm->set('showForm', false);
};

$handlers['selectAll'] = function() use ($sm) {
    $page = $sm->get('pagedData', []);
    $selected = $sm->get('selectedRows', []);
    foreach ($page as $row) {
        if (!in_array($row[0], $selected)) $selected[] = $row[0];
    }
    $sm->set('selectedRows', $selected);
    echo "选中本页所有行\n";
};

$handlers['invertSelect'] = function() use ($sm) {
    $page = $sm->get('pagedData', []);
    $selected = $sm->get('selectedRows', []);
    foreach ($page as $row) {
        if (in_array($row[0], $selected)) {
            $selected = array_values(array_filter($selected, fn($v)=>$v!=$row[0]));
        } else {
            $selected[] = $row[0];
        }
    }
    $sm->set('selectedRows', $selected);
    echo "翻转本页选择\n";
};

$handlers['deleteSelected'] = function() use ($sm) {
    $selected = $sm->get('selectedRows', []);
    if (empty($selected)) { echo "没有选中行\n"; return; }
    $all = $sm->get('allData', []);
    $remaining = array_values(array_filter($all, fn($r)=>!in_array($r[0], $selected)));
    $sm->set('allData', $remaining);
    $sm->set('selectedRows', []);
    $sm->set('filtered', $remaining);
    recomputePaging($sm);
    echo "已删除选中行: " . count($selected) . " 条\n";
};

$handlers['prevPage'] = function() use ($sm) {
    $page = max(1, (int)$sm->get('page',1)-1);
    $sm->set('page',$page);
    recomputePaging($sm);
};
$handlers['nextPage'] = function() use ($sm) {
    $page = (int)$sm->get('page',1)+1;
    $sm->set('page',$page);
    recomputePaging($sm);
};

$handlers['exportCsv'] = function() use ($sm) {
    $all = $sm->get('filtered', []);
    $lines = [];
    foreach ($all as $row) {
        $lines[] = implode(',', array_map(fn($c)=>str_replace(',','',$c), $row));
    }
    $content = implode("\n", $lines);
    $file = __DIR__ . '/complex_export_' . date('YmdHis') . '.csv';
    file_put_contents($file, $content);
    echo "已导出 CSV: {$file}\n";
};

// 监听 selectedRows 变化，打印当前选中
$sm->watch('selectedRows', function($sel){ echo "当前选中: " . json_encode($sel) . "\n"; });

// 初次计算分页
recomputePaging($sm);

// 渲染并运行
$app = HtmlRenderer::render(__DIR__ . '/views/complex_table_demo.ui.html', $handlers);
$app->build();

// 仅在 GUI 环境下 show + main
if (getenv('RUN_GUI') === '1') {
    $app->show();
    App::main();
} else {
    echo "示例已构建（无 GUI 模式）。要打开 GUI，请使用: RUN_GUI=1 php example/complex_table_demo.php\n";
}

