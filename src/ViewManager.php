<?php

namespace Kingbes\Libui\View;

use Kingbes\Libui\View\Template\BladeGuiRenderer;
use Kingbes\Libui\View\Template\TemplateRenderer;

class ViewManager
{
    private static ?ViewManager $instance = null;
    private array $viewPaths = [];
    private array $templateEngines = [];
    private array $globalData = [];
    private array $globalHandlers = [];

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function init(): void
    {
        // 默认视图路径
        $this->addViewPath(__DIR__ . '/../../resources/views');
        $this->addViewPath(getcwd() . '/resources/views');
        $this->addViewPath(getcwd() . '/views');
        $this->addViewPath(getcwd() . '/templates');

        // 注册默认模板引擎
        $this->registerEngine('xml', new TemplateRenderer());
        $this->registerEngine('gui', new TemplateRenderer());
        $this->registerEngine('blade.gui', new BladeGuiRenderer());
    }

    /**
     * 添加视图路径
     */
    public function addViewPath(string $path): self
    {
        if (is_dir($path)) {
            $this->viewPaths[] = rtrim($path, '/') . '/';
        }
        return $this;
    }

    /**
     * 设置视图路径（替换所有）
     */
    public function setViewPaths(array $paths): self
    {
        $this->viewPaths = [];
        foreach ($paths as $path) {
            $this->addViewPath($path);
        }
        return $this;
    }

    /**
     * 注册模板引擎
     */
    public function registerEngine(string $extension, $engine): self
    {
        $this->templateEngines[$extension] = $engine;
        return $this;
    }

    /**
     * 设置全局数据
     */
    public function share(array $data): self
    {
        $this->globalData = array_merge($this->globalData, $data);
        return $this;
    }

    /**
     * 注册全局事件处理器
     */
    public function handlers(array $handlers): self
    {
        $this->globalHandlers = array_merge($this->globalHandlers, $handlers);
        return $this;
    }

    /**
     * 查找视图文件
     */
    public function findView(string $name): ?string
    {
        $extensions = array_keys($this->templateEngines);

        // 如果已经包含扩展名
        if ($this->hasExtension($name)) {
            return $this->searchInPaths($name);
        }

        // 尝试各种扩展名
        foreach ($extensions as $ext) {
            $filename = $name . '.' . $ext;
            $found = $this->searchInPaths($filename);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    /**
     * 渲染视图
     */
    public function render(string $view, array $data = [], array $handlers = []): ComponentBuilder
    {
        $viewPath = $this->findView($view);

        if (!$viewPath) {
            throw new \Exception("View not found: {$view}");
        }

        // 合并数据和处理器
        $mergedData = array_merge($this->globalData, $data);
        $mergedHandlers = array_merge($this->globalHandlers, $handlers);

        // 获取对应的模板引擎
        $extension = $this->getFileExtension($viewPath);
        $engine = $this->templateEngines[$extension] ?? $this->templateEngines['xml'];

        // 渲染模板
        $content = file_get_contents($viewPath);
        return $engine->render($content, $mergedData, $mergedHandlers);
    }

    /**
     * 在所有路径中搜索文件
     */
    private function searchInPaths(string $filename): ?string
    {
        foreach ($this->viewPaths as $path) {
            $fullPath = $path . $filename;
            if (file_exists($fullPath)) {
                return $fullPath;
            }

            // 支持子目录
            $fullPath = $path . str_replace('.', '/', $filename);
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }

        return null;
    }

    /**
     * 获取文件扩展名
     */
    private function getFileExtension(string $path): string
    {
        $parts = explode('.', basename($path));

        // 处理复合扩展名如 .blade.gui
        if (count($parts) >= 3) {
            return implode('.', array_slice($parts, -2));
        } elseif (count($parts) >= 2) {
            return end($parts);
        }

        return 'xml'; // 默认
    }

    /**
     * 检查文件名是否已包含扩展名
     */
    private function hasExtension(string $name): bool
    {
        return str_contains($name, '.');
    }
}