<?php

use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Components\BarChartBuilder;
use Kingbes\Libui\View\Components\ChartBuilder;
use Kingbes\Libui\View\Components\PieChartBuilder;

/**
 * ChartPlugin - 示例图表组件插件
 * 
 * 这是一个演示如何扩展 libuiBuilder 的插件示例
 */
class ChartPlugin
{
    /**
     * 注册插件
     */
    public static function register(): void
    {
        // 注册图表组件创建方法
        Builder::extend('chart', function(array $config = []) {
            return new ChartBuilder($config);
        });

        // 注册饼图组件创建方法
        Builder::extend('pieChart', function(array $config = []) {
            return new PieChartBuilder($config);
        });

        // 注册柱状图组件创建方法
        Builder::extend('barChart', function(array $config = []) {
            return new BarChartBuilder($config);
        });
    }
}