<?php

namespace Kingbes\Libui\View;

use Kingbes\Libui\App;
use Kingbes\Libui\View\Template\TemplateRenderer;

if (!function_exists('Kingbes\Libui\View\view')) {
    /**
     * 渲染视图并显示
     */
    function view(string $name, array $data = [], array $handlers = []): void
    {
        App::init();
        $component = ViewManager::instance()->render($name, $data, $handlers);
        $component->show();
    }
}

if (!function_exists('Kingbes\Libui\View\component')) {
    /**
     * 渲染视图组件但不显示
     */
    function component(string $name, array $data = [], array $handlers = []): ComponentBuilder
    {
        return ViewManager::instance()->render($name, $data, $handlers);
    }
}

if (!function_exists('Kingbes\Libui\View\render')) {
    /**
     * 直接渲染模板字符串
     */
    function render(string $template, array $data = [], array $handlers = []): void
    {
        App::init();
        $renderer = new TemplateRenderer();
        $component = $renderer->render($template, $data, $handlers);
        $component->show();
    }
}

if (!function_exists('Kingbes\Libui\View\config')) {
    /**
     * 配置视图系统
     */
    function config(): ViewManager
    {
        return ViewManager::instance();
    }
}

if (!function_exists('Kingbes\Libui\View\share')) {
    /**
     * 设置全局数据
     */
    function share(array $data): void
    {
        ViewManager::instance()->share($data);
    }
}

if (!function_exists('Kingbes\Libui\View\handlers')) {
    /**
     * 设置全局事件处理器
     */
    function handlers(array $handlers): void
    {
        ViewManager::instance()->handlers($handlers);
    }
}

if (!function_exists('Kingbes\Libui\View\addViewPath')) {
    /**
     * 添加视图路径
     */
    function addViewPath(string $path): void
    {
        ViewManager::instance()->addViewPath($path);
    }
}