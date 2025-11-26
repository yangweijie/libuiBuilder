<?php

namespace Kingbes\Libui\Declarative\Components;

use FFI\CData;
use Kingbes\Libui\App;
use Kingbes\Libui\Window;

class WindowComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:window';
    }
    
    // 定义组件支持的属性
    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'title', 'width', 'height', 'menubar', 'margined'
        ]);
    }

    public function render(): CData
    {
        $title = $this->getAttribute('title', 'Untitled');
        $width = (int)$this->getAttribute('width', 640);
        $height = (int)$this->getAttribute('height', 480);
        $hasMenubar = (bool)$this->getAttribute('menubar', false);

        $window = Window::create($title, $width, $height, $hasMenubar);

        // 设置关闭事件
        if ($onClose = $this->getAttribute('on-close')) {
            Window::onClosing($window, $onClose);
        }else{
            Window::onClosing($window, function () use ($window) {
                echo "窗口关闭\n";
                App::quit();
                return true;
            });
        }

        // 渲染子组件
        if (count($this->children) === 1) {
            $child = $this->children[0]->render();
            Window::setChild($window, $child);
        }

        $this->handle = $window;
        return $window;
    }

    // 重写应用属性方法
    protected function applyAttribute(string $attributeName, $value): void
    {
        if ($attributeName === 'title' && $this->handle) {
            // 如果是 title 属性，更新窗口标题
            Window::setTitle($this->handle, (string)$value);
        } elseif ($attributeName === 'width' && $this->handle) {
            // 如果是 width 属性，窗口大小不能直接修改，可能需要重新创建
            // 这里可以发出警告或记录日志
        } elseif ($attributeName === 'height' && $this->handle) {
            // 如果是 height 属性，窗口大小不能直接修改，可能需要重新创建
            // 这里可以发出警告或记录日志
        }
        // 也可以处理其他属性
        parent::applyAttribute($attributeName, $value);
    }

    public function getValue()
    {
        // TODO: Implement getValue() method.
    }

    public function setValue($value): void
    {
        // TODO: Implement setValue() method.
    }
}