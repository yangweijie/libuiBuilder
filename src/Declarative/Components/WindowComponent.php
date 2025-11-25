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

    public function getValue()
    {
        // TODO: Implement getValue() method.
    }

    public function setValue($value): void
    {
        // TODO: Implement setValue() method.
    }
}