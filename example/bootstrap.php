<?php

// 用户项目中的 bootstrap.php 或 config.php

require_once __DIR__.'/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\State\StateManager;
use function Kingbes\Libui\View\{config, share, handlers};

// 配置自定义视图路径
config()
    ->addViewPath(__DIR__ . '/views')      // 应用视图
    ->addViewPath(__DIR__ . '/resources/gui')  // GUI资源
    ->addViewPath(__DIR__ . '/components');    // 组件模板

// 设置全局数据
share([
    'app_name' => 'My LibUI App',
    'version' => '1.0.0',
    'theme' => 'light',
    'user' => null
]);

// 注册全局事件处理器
handlers([
    'quit' => function () {
        echo "应用退出\n";
        App::quit();
    },
    'about' => function () {
        $appName = StateManager::instance()->get('app_name');
        echo "关于 {$appName}\n";
    },
    'error' => function ($message) {
        echo "错误: {$message}\n";
    }
]);