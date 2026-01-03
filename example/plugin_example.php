<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\PluginLoader;

// 加载插件
PluginLoader::loadPlugin(__DIR__ . '/../plugins/ChartPlugin.php');

// 检查扩展方法是否存在
var_dump('可用扩展:', Builder::getExtensions());

// 使用扩展方法
$chart = Builder::chart()
    ->title('销售数据')
    ->type('line')
    ->data([1, 2, 3, 4, 5])
    ->get();

echo "图表组件创建成功！\n";

// 批量加载插件目录
$result = PluginLoader::loadPlugins(__DIR__ . '/../plugins');
var_dump('插件加载结果:', $result);