<?php

use Kingbes\Libui\App;
use Kingbes\Libui\Control;
use Kingbes\Libui\Declarative\ComponentRegistry;
use Kingbes\Libui\Declarative\Components\ButtonComponent;
use Kingbes\Libui\Declarative\Components\EntryComponent;
use Kingbes\Libui\Declarative\Components\FormComponent;
use Kingbes\Libui\Declarative\Components\TemplateParser;
use Kingbes\Libui\Declarative\Components\WindowComponent;
use Kingbes\Libui\Declarative\ErrorHandler;
use Kingbes\Libui\Declarative\EventBus;
use Kingbes\Libui\Declarative\StateManager;

ini_set('date.timezone', 'Asia/Shanghai');
require_once __DIR__.'/../vendor/autoload.php';

// 注册全局错误处理器
ErrorHandler::register();

function printUsername(){
    echo getState('form.username', '未设置');
}

// 简单测试模板
$testTemplate = '
<ui:window title="简单测试" width="400" height="300" ref="mainWindow">
    <ui:form padded="true">
        <ui:entry label="用户名" ref="username" v-model="form.username" />
        <ui:button ref="showUsername" text="显示用户名" @click="echo printUsername() . PHP_EOL;" />
    </ui:form>
</ui:window>';

// 注册组件
App::init();
ComponentRegistry::register('ui:window', WindowComponent::class);
ComponentRegistry::register('ui:form', FormComponent::class);
ComponentRegistry::register('ui:entry', EntryComponent::class);
ComponentRegistry::register('ui:button', ButtonComponent::class);

// 初始化状态
StateManager::set('form', ['username' => '']);

// 启用调试模式
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 渲染界面
$parser = new TemplateParser();
$app = $parser->parse($testTemplate);
$window = $app->render();

echo "=== 简单测试应用启动 ===\n";

Control::show($window);
App::main();