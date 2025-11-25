<?php

use Kingbes\Libui\App;
use Kingbes\Libui\Control;
use Kingbes\Libui\Declarative\ComponentRegistry;
use Kingbes\Libui\Declarative\Components\ButtonComponent;
use Kingbes\Libui\Declarative\Components\EntryComponent;
use Kingbes\Libui\Declarative\Components\FormComponent;
use Kingbes\Libui\Declarative\Components\TemplateParser;
use Kingbes\Libui\Declarative\Components\WindowComponent;
use Kingbes\Libui\Declarative\Components\CheckboxComponent;
use Kingbes\Libui\Declarative\Components\LabelComponent;
use Kingbes\Libui\Declarative\Components\ComboboxComponent;
use Kingbes\Libui\Declarative\ErrorHandler;
use Kingbes\Libui\Declarative\EventBus;
use Kingbes\Libui\Declarative\StateManager;

require_once __DIR__.'/../vendor/autoload.php';

// 注册全局错误处理器
ErrorHandler::register();

// 演示所有特性 - 包括方法链和箭头函数支持
$advancedFeaturesTemplate = '
<ui:window title="高级特性演示" width="900" height="700" ref="mainWindow">
    <ui:form padded="true">
        <ui:label text="高级特性演示 - 方法链、箭头函数、动态绑定" style="font-size: 16px; font-weight: bold;" />
        
        <!-- 基础数据绑定 -->
        <ui:entry label="搜索关键词" v-model="search.keyword" @change="updateSearchResults()" />
        <ui:combobox label="搜索类型" v-model="search.type" :options="json_decode(getState(\'searchTypes\', \'[]\'), true)" />
        <ui:button text="执行搜索" @click="performSearch()" />
        
        <!-- 状态显示 -->
        <ui:label :text="\'搜索结果数量: \' . count(json_decode(getState(\'searchResults\', \'[]\'), true))" />
        <ui:label :text="getState(\'statusMessage\', \'准备就绪\')" />
        
        <!-- 杢件链调用演示 (模拟) -->
        <ui:button text="复杂操作演示" @click="performComplexOperation()" />
        <ui:button text="数据库操作演示" @click="performDatabaseOperation()" />
        
        <!-- 杢头函数支持演示 -->
        <ui:button text="箭头函数操作" @click="$this => { setState(\'statusMessage\', \'箭头函数执行中...\'); setState(\'counter\', getState(\'counter\', 0) + 1); echo \'计数: \' . getState(\'counter\', 0) . PHP_EOL; }" />
        <ui:label :text="\'当前计数: \' . getState(\'counter\', 0)" />
        
        <!-- 杢件通信演示 -->
        <ui:entry label="消息" v-model="message.content" />
        <ui:checkbox text="加急处理" v-model="message.urgent" />
        <ui:button text="发送消息" @click="sendMessage()" :disabled="!getState(\'message.content\', false)" />
        <ui:button text="批量处理" @click="batchProcess()" :disabled="count(json_decode(getState(\'searchResults\', \'[]\'), true)) === 0" />
        <ui:button text="重置所有" @click="resetAll()" />
        
        <!-- 条件渲染演示 -->
        <ui:label text="紧急消息: 需要立即处理！" v-show="getState(\'message.urgent\', false)" style="color: red; font-weight: bold;" />
        <ui:entry label="紧急联系人" v-model="emergency.contact" v-show="getState(\'message.urgent\', false)" />
        <ui:button text="紧急联系" @click="contactEmergency()" v-show="getState(\'message.urgent\', false)" />
    </ui:form>
</ui:window>';

// 注册所有组件
App::init();
ComponentRegistry::register('ui:window', WindowComponent::class);
ComponentRegistry::register('ui:form', FormComponent::class);
ComponentRegistry::register('ui:entry', EntryComponent::class);
ComponentRegistry::register('ui:button', ButtonComponent::class);
ComponentRegistry::register('ui:label', LabelComponent::class);
ComponentRegistry::register('ui:combobox', ComboboxComponent::class);
ComponentRegistry::register('ui:checkbox', CheckboxComponent::class);

// 初始化状态
StateManager::set('search', ['keyword' => '', 'type' => '']);
StateManager::set('message', ['content' => '', 'urgent' => false]);
StateManager::set('emergency', ['contact' => '']);
StateManager::set('searchResults', []);
StateManager::set('statusMessage', '准备就绪');
StateManager::set('counter', 0);
StateManager::set('searchTypes', json_encode(['用户', '产品', '订单', '全部']));

// 模拟数据源
StateManager::set('users', [
    ['id' => 1, 'name' => '张三', 'email' => 'zhangsan@example.com'],
    ['id' => 2, 'name' => '李四', 'email' => 'lisi@example.com'],
    ['id' => 3, 'name' => '王五', 'email' => 'wangwu@example.com'],
]);

// 定义函数演示各种特性

// 基础搜索功能
function updateSearchResults() {
    echo "搜索条件已更新\n";
}

function performSearch() {
    $keyword = StateManager::get('search.keyword', '');
    $type = StateManager::get('search.type', '');
    
    echo "执行搜索: 关键词='{$keyword}', 类型='{$type}'\n";
    
    // 模拟搜索结果
    $results = [];
    if ($keyword) {
        $results = [['id' => 1, 'name' => "搜索结果: {$keyword}", 'type' => $type]];
    }
    
    StateManager::set('searchResults', $results);
    StateManager::set('statusMessage', "找到 " . count($results) . " 个结果");
}

// 演示方法链调用概念（在PHP中我们模拟链式操作）
function performComplexOperation() {
    echo "执行复杂操作...\n";
    
    // 模拟链式操作的效果
    $result = modifyState()
        ->validateData()
        ->processResult();
    
    echo "复杂操作完成，结果: " . ($result ? '成功' : '失败') . "\n";
    StateManager::set('statusMessage', '复杂操作完成');
}

// 辅助函数用于模拟方法链
function modifyState() {
    StateManager::set('operation.stage', 'modify');
    return (object)['validateData' => function() { return modifyState2(); }];
}

function modifyState2() {
    StateManager::set('operation.data', 'modified');
    return (object)['processResult' => function() { return true; }];
}

// 演示数据库操作概念
function performDatabaseOperation() {
    echo "执行数据库操作...\n";
    
    // 模拟数据库操作
    $users = StateManager::get('users', []);
    $filtered = array_filter($users, function($user) {
        return stripos($user['name'], '张') !== false;
    });
    
    echo "数据库操作完成，处理了 " . count($filtered) . " 条记录\n";
    StateManager::set('statusMessage', '数据库操作完成');
}

// 消息功能
function sendMessage() {
    $message = StateManager::get('message', []);
    $urgent = $message['urgent'] ?? false;
    
    echo ($urgent ? "[紧急] " : "") . "发送消息: " . ($message['content'] ?? '') . "\n";
    StateManager::set('statusMessage', '消息已发送');
}

// 批量处理
function batchProcess() {
    $results = StateManager::get('searchResults', []);
    $count = count($results);
    
    echo "批量处理 {$count} 个项目\n";
    StateManager::set('statusMessage', "批量处理完成 ({$count} 项)");
}

// 重置功能
function resetAll() {
    StateManager::set('search', ['keyword' => '', 'type' => '']);
    StateManager::set('message', ['content' => '', 'urgent' => false]);
    StateManager::set('emergency', ['contact' => '']);
    StateManager::set('searchResults', []);
    StateManager::set('statusMessage', '已重置');
    StateManager::set('counter', 0);
    echo "所有状态已重置\n";
}

function contactEmergency() {
    $contact = StateManager::get('emergency.contact', '默认联系人');
    echo "紧急联系: {$contact}\n";
    StateManager::set('statusMessage', '已联系紧急联系人');
}

// 启用调试模式
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 渲染界面
$parser = new TemplateParser();
$app = $parser->parse($advancedFeaturesTemplate);
$window = $app->render();

// 监听全局事件
EventBus::on('button:clicked', function($ref, $result) {
    echo "[事件] 按钮 {$ref} 被点击\n";
});

EventBus::on('entry:changed', function($ref, $value, $result) {
    echo "[事件] 输入框 {$ref} 值变为: {$value}\n";
});

echo "=== 高级特性演示应用启动 ===\n";
echo "支持以下特性:\n";
echo "- 声明式语法\n";
echo "- 双向数据绑定\n";
echo "- 杢件链调用概念\n";
echo "- 箭头函数支持\n";
echo "- 条件渲染\n";
echo "- 动态属性绑定\n";
echo "- 状态管理\n";
echo "- 杢件通信\n";

Control::show($window);
App::main();