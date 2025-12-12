<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 创建HTML模板文件
$htmlTemplate = <<<'HTML'
<window title="员工管理系统" size="900,600">
    <grid padded="true">
        <label row="0" col="0" colspan="4" align="center" size="large">
            员工信息管理系统
        </label>
        
        <separator row="1" col="0" colspan="4" expand="horizontal"/>
        
        <label row="2" col="0">搜索:</label>
        <input row="2" col="1" bind="searchText" placeholder="输入姓名或职位搜索" expand="horizontal"/>
        <button row="2" col="2" onclick="handleSearch">搜索</button>
        <button row="2" col="3" onclick="handleReset">重置</button>
        
        <separator row="3" col="0" colspan="4" expand="horizontal"/>
        
        <!-- 表格组件 -->
        <?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\StateManager;

App::init();

// 创建HTML模板文件
$htmlTemplate = <<<'HTML'
<window title="员工管理系统" size="900,600">
    <grid padded="true">
        <label row="0" col="0" colspan="4" align="center" size="large">
            员工信息管理系统
        </label>
        
        <separator row="1" col="0" colspan="4" expand="horizontal"/>
        
        <label row="2" col="0">搜索:</label>
        <input row="2" col="1" bind="searchText" placeholder="输入姓名或职位搜索" expand="horizontal"/>
        <button row="2" col="2" onclick="handleSearch">搜索</button>
        <button row="2" col="3" onclick="handleReset">重置</button>
        
        <separator row="3" col="0" colspan="4" expand="horizontal"/>
        
        <!-- 表格组件 - 使用简单的文本列 -->
        <table row="4" col="0" colspan="4" expand="both" bind="employeeTable">
           <thead>
                <tr>
                    <th>ID</th>
                    <th>姓名</th>
                    <th>职位</th>
                    <th>在职状态</th>
                    <th>项目进度</th>
                    <th>入职时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>001</td>
                    <td>张三</td>
                    <td>前端工程师</td>
                    <td>true</td>
                    <td>85</td>
                    <td>2023-01-15</td>
                    <td>编辑:edit</td>
                </tr>
                <tr>
                    <td>002</td>
                    <td>李四</td>
                    <td>后端工程师</td>
                    <td>true</td>
                    <td>60</td>
                    <td>2023-03-20</td>
                    <td>查看:view</td>
                </tr>
                <tr>
                    <td>003</td>
                    <td>王五</td>
                    <td>UI设计师</td>
                    <td>false</td>
                    <td>45</td>
                    <td>2022-11-10</td>
                    <td>激活:activate</td>
                </tr>
                <tr>
                    <td>004</td>
                    <td>赵六</td>
                    <td>产品经理</td>
                    <td>true</td>
                    <td>90</td>
                    <td>2023-05-05</td>
                    <td>编辑:edit</td>
                </tr>
                <tr>
                    <td>005</td>
                    <td>钱七</td>
                    <td>测试工程师</td>
                    <td>true</td>
                    <td>75</td>
                    <td>2023-02-28</td>
                    <td>查看:view</td>
                </tr>
            </tbody>
        </table>
        
        <separator row="9" col="0" colspan="4" expand="horizontal"/>
        
        <hbox row="10" col="0" colspan="4" align="center">
            <button onclick="handleAdd">添加员工</button>
            <button onclick="handleExport">导出数据</button>
            <button onclick="handleRefresh">刷新表格</button>
            <button onclick="handleExit">退出</button>
        </hbox>
        
        <label row="11" col="0" colspan="4" align="center" bind="statusText">
            共5条记录
        </label>
    </grid>
</window>
HTML;

// 创建临时HTML文件
$tempFile = tempnam(sys_get_temp_dir(), 'table_demo_') . '.ui.html';
file_put_contents($tempFile, $htmlTemplate);

// 事件处理器
$handlers = [
    'handleSearch' => function($button, $state) {
        $searchText = $state->get('searchText');
        if (!empty($searchText)) {
            echo "搜索关键词: {$searchText}\n";
            $state->set('statusText', "正在搜索: {$searchText}");
        } else {
            echo "请输入搜索关键词\n";
        }
    },
    
    'handleReset' => function($button, $state) {
        $state->set('searchText', '');
        $state->set('statusText', '搜索已重置');
        echo "搜索条件已重置\n";
    },
    
    'handleAdd' => function($button, $state) {
        echo "添加新员工\n";
        $state->set('statusText', '正在添加新员工...');
    },
    
    'handleExport' => function($button, $state) {
        echo "导出员工数据\n";
        $state->set('statusText', '正在导出数据...');
    },
    
    'handleRefresh' => function($button, $state) {
        echo "刷新表格数据\n";
        $state->set('statusText', '表格已刷新');
    },
    
    'handleExit' => function($button, $state) {
        echo "退出应用程序\n";
        App::quit();
    }
];

// 初始化状态
$state = StateManager::instance();
$state->set('searchText', '');
$state->set('statusText', '共5条记录');

// 渲染HTML模板
try {
    $app = HtmlRenderer::render($tempFile, $handlers);
    $app->show();
} finally {
    // 清理临时文件
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
}

