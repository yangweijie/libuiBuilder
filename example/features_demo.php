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
ini_set('date.timezone', 'Asia/Shanghai');
// 特性演示模板
$featuresDemoTemplate = <<<XML
<ui:window title="特性演示" width="800" height="600" ref="mainWindow" margined="true">
    <ui:form padded="true">
        <ui:label text="声明式组件系统演示" />
        
        <!-- 双向数据绑定演示 -->
        <ui:entry label="用户名" ref="username" v-model="form.username" />
        <ui:label :text="用户名长度: " />
        
        <!-- 条件渲染演示 -->
        <ui:entry label="角色" ref="role" v-model="form.role" />
        <ui:entry label="管理员密码" v-model="form.adminPassword" v-show="getState('form.role', '') === 'admin'" />
        <ui:button text="删除用户" @click="deleteUser()" v-show="getState('form.role', '') === 'admin'" />
        
        <!-- 动态属性演示 -->
        <ui:entry label="邮箱" v-model="form.email" :disabled="strlen(getState('form.username', false)) > 0" />
        <ui:button text="注册" @click="registerUser()" :disabled="!getState('form.username', false) || !getState('form.email', false)" />
        <ui:label :text="getState('form.loading', false) ? '保存中...' : '准备就绪'" />
        
        <!-- 动态选项演示 -->
        <ui:combobox label="城市" v-model="form.city" :options="json_decode(getState('cities', '[]'), true)" />
        <ui:label :text="'选中的城市: ' . getState('form.city', '')" />
        
        <!-- 事件系统演示 -->
        <ui:button text="设置加载状态" @click="setState('form.loading', true); echo '设置为加载状态';" />
        <ui:button text="取消加载状态" @click="setState('form.loading', false); echo '取消加载状态';" />
        <ui:button text="重置表单" @click="resetForm()" />
        <ui:button text="显示当前状态" @click="showCurrentState()" />
        <ui:checkbox text="同意服务条款" v-model="form.agreeTerms" />
        <ui:button text="提交" @click="submitForm()" :disabled="!getState('form.agreeTerms', false)" />
    </ui:form>
</ui:window>
XML;

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
StateManager::set('form', [
    'username' => '',
    'email' => '',
    'role' => '',
    'adminPassword' => '',
    'city' => '',
    'agreeTerms' => false,
    'loading' => false
]);

// 设置城市选项
StateManager::set('cities', json_encode(['北京', '上海', '广州', '深圳', '杭州']));

// 定义函数
function deleteUser() {
    echo "删除用户操作\n";
    $role = StateManager::get('form.role');
    if ($role === 'admin') {
        echo "不能删除管理员账户\n";
    } else {
        echo "用户删除成功\n";
    }
}

function registerUser() {
    echo "注册用户操作\n";
    $form = StateManager::get('form', []);
    echo "用户名: {$form['username']}, 邮箱: {$form['email']}\n";
}

function resetForm() {
    StateManager::set('form', [
        'username' => '',
        'email' => '',
        'role' => '',
        'adminPassword' => '',
        'city' => '',
        'agreeTerms' => false,
        'loading' => false
    ]);
    echo "表单已重置\n";
}

function showCurrentState() {
    $form = StateManager::get('form', []);
    echo "当前表单状态:\n";
    foreach ($form as $key => $value) {
        echo "  {$key}: {$value}\n";
    }
}

function submitForm() {
    echo "提交表单操作\n";
    $form = StateManager::get('form', []);
    var_dump($form);
    if (!$form['agreeTerms']) {
        echo "请先同意服务条款\n";
        return;
    }
    echo "表单提交成功\n";
}

// 启用调试模式
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 渲染界面
$parser = new TemplateParser();
$app = $parser->parse($featuresDemoTemplate);
$window = $app->render();

// 监听全局事件
EventBus::on('button:clicked', function($ref, $result) {
    echo "[GLOBAL] Button {$ref} clicked with result: " . print_r($result, true) . "\n";
});

EventBus::on('entry:changed', function($ref, $value, $result) {
    echo "[GLOBAL] Entry {$ref} changed to: {$value}, result: " . print_r($result, true) . "\n";
});

echo "=== 特性演示应用启动 ===\n";
echo "支持以下特性:\n";
echo "- 声明式语法\n";
echo "- 双向数据绑定 (v-model)\n";
echo "- 条件渲染 (v-show)\n";
echo "- 动态属性 (:disabled, :text)\n";
echo "- 事件系统 (@click)\n";
echo "- 状态管理\n";

Control::show($window);
App::main();