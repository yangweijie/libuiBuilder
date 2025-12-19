<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Window;
use Kingbes\Libui\Control;
use Kingbes\Libui\View\State\StateManager;

/**
 * 窗口构建器
 * 
 * 支持链式调用的窗口构建器
 */
class WindowBuilder extends ComponentBuilder
{
    /** @var array 子组件列表 */
    protected array $children = [];

    /**
     * 设置窗口标题
     *
     * @param string $title 标题
     * @return $this
     */
    public function title(string $title): self
    {
        $this->config['title'] = $title;
        return $this;
    }

    /**
     * 设置窗口大小
     *
     * @param int $width 宽度
     * @param int $height 高度
     * @return $this
     */
    public function size(int $width, int $height): self
    {
        $this->config['width'] = $width;
        $this->config['height'] = $height;
        return $this;
    }

    /**
     * 设置窗口是否可调整大小
     *
     * @param bool $resizable 是否可调整大小
     * @return $this
     */
    public function resizable(bool $resizable): self
    {
        $this->config['resizable'] = $resizable;
        return $this;
    }

    /**
     * 设置窗口是否有边距
     *
     * @param bool $margined 是否有边距
     * @return $this
     */
    public function margined(bool $margined): self
    {
        $this->config['margined'] = $margined;
        return $this;
    }

    /**
     * 设置窗口是否有菜单条
     *
     * @param bool $hasMenubar 是否有菜单条
     * @return $this
     */
    public function menubar(bool $hasMenubar): self
    {
        $this->config['hasMenubar'] = $hasMenubar;
        return $this;
    }

    /**
     * 设置子组件
     *
     * @param array|ComponentBuilder $children 子组件数组或单个组件
     * @return $this
     */
    public function contains($children): self
    {
        if (is_array($children)) {
            $this->children = $children;
        } else {
            $this->children = [$children];
        }
        return $this;
    }

    /**
     * 添加子组件
     *
     * @param ComponentBuilder $child 子组件
     * @return $this
     */
    public function append(ComponentBuilder $child): self
    {
        $this->children[] = $child;
        return $this;
    }

    /**
     * 注册窗口关闭事件
     *
     * @param callable $callback 回调函数
     * @return $this
     */
    public function onClosing(callable $callback): self
    {
        $this->events['onClosing'] = $callback;
        return $this;
    }

    /**
     * 注册窗口大小改变事件
     *
     * @param callable $callback 回调函数
     * @return $this
     */
    public function onSizeChanged(callable $callback): self
    {
        $this->events['onSizeChanged'] = $callback;
        return $this;
    }

    /**
     * 注册窗口位置改变事件
     *
     * @param callable $callback 回调函数
     * @return $this
     */
    public function onPositionChanged(callable $callback): self
    {
        $this->events['onPositionChanged'] = $callback;
        return $this;
    }

    /**
     * 注册窗口焦点改变事件
     *
     * @param callable $callback 回调函数
     * @return $this
     */
    public function onFocusChanged(callable $callback): self
    {
        $this->events['onFocusChanged'] = $callback;
        return $this;
    }

    /**
     * 构建窗口
     *
     * @return CData 窗口句柄
     */
    public function build(): CData
    {
        // 获取配置
        $title = $this->config['title'] ?? 'Untitled Window';
        $width = $this->config['width'] ?? 800;
        $height = $this->config['height'] ?? 600;
        $hasMenubar = $this->config['hasMenubar'] ?? false;

        // 创建窗口
        $this->handle = Window::create($title, $width, $height, $hasMenubar ? 1 : 0);

        // 设置可调整大小
        if (isset($this->config['resizable'])) {
            Window::setResizable($this->handle, $this->config['resizable']);
        }

        // 设置边距
        if (isset($this->config['margined'])) {
            Window::setMargined($this->handle, $this->config['margined']);
        }

        // 构建子组件
        if (!empty($this->children)) {
            // 如果只有一个子组件，直接设置
            if (count($this->children) === 1) {
                $child = $this->children[0];
                $childHandle = $child->build();
                Window::setChild($this->handle, $childHandle);
            } else {
                // 多个子组件，使用 VBox 包装
                $vbox = (new BoxBuilder('vertical'))->contains($this->children)->build();
                Window::setChild($this->handle, $vbox);
            }
        }

        // 绑定事件
        $this->bindEvents();

        // 注册到状态管理器
        if ($this->id && $this->stateManager) {
            $this->stateManager->registerComponent($this->id, $this);
        }

        return $this->handle;
    }

    /**
     * 显示窗口
     *
     * @return void
     */
    public function show(): void
    {
        if (!$this->handle) {
            $this->build();
        }
        Control::show($this->handle);
    }

    /**
     * 绑定事件处理器
     *
     * @return void
     */
    protected function bindEvents(): void
    {
        if (!$this->handle) {
            return;
        }

        // 关闭事件
        if (isset($this->events['onClosing'])) {
            $callback = $this->events['onClosing'];
            Window::onClosing($this->handle, function() use ($callback) {
                return $callback();
            });
        }

        // 大小改变事件
        if (isset($this->events['onSizeChanged'])) {
            $callback = $this->events['onSizeChanged'];
            Window::onContentSizeChanged($this->handle, function() use ($callback) {
                $callback($this);
            });
        }

        // 位置改变事件
        if (isset($this->events['onPositionChanged'])) {
            $callback = $this->events['onPositionChanged'];
            Window::onPositionChanged($this->handle, function() use ($callback) {
                $callback($this);
            });
        }

        // 焦点改变事件
        if (isset($this->events['onFocusChanged'])) {
            $callback = $this->events['onFocusChanged'];
            Window::onFocusChanged($this->handle, function() use ($callback) {
                $callback($this);
            });
        }
    }

    /**
     * 获取组件类型
     *
     * @return string
     */
    public function getType(): string
    {
        return 'window';
    }

    /**
     * 获取子组件
     *
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}
