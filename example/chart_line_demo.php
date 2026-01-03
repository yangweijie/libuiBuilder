<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\PluginLoader;

// 加载插件
PluginLoader::loadPlugin(__DIR__ . '/../plugins/ChartPlugin.php');

echo "创建折线图示例...\n";

// 创建折线图
$chart = Builder::chart()
    ->title('销售数据趋势')
    ->width(600)
    ->height(400)
    ->type('line')
    ->data([10, 25, 30, 45, 50, 65, 70, 85, 90, 100])
    ->color([0.2, 0.4, 0.8, 1.0]) // 蓝色
    ->get();

echo "折线图创建成功！\n";

// 创建柱状图
$barChart = Builder::chart()
    ->title('月度销售额')
    ->width(500)
    ->height(300)
    ->type('bar')
    ->data([120, 85, 95, 110, 130, 105])
    ->color([0.2, 0.8, 0.4, 1.0]) // 绿色
    ->get();

echo "柱状图创建成功！\n";

// 创建饼图
$pieChart = Builder::chart()
    ->title('市场份额')
    ->width(300)
    ->height(300)
    ->type('pie')
    ->data([30, 25, 20, 15, 10])
    ->get();

echo "饼图创建成功！\n";

echo "所有图表组件创建完成！\n";