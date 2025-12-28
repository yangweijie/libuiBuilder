<?php

namespace Kingbes\Libui\View\Components;

use FFI\CData;
use Kingbes\Libui\App;
use Kingbes\Libui\Control;
use Kingbes\Libui\View\Validation\ComponentBuilder;
use Kingbes\Libui\Window;

class WindowBuilder extends ComponentBuilder
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    public function getDefaultConfig(): array
    {
        return [
            'title' => 'LibUI Application',
            'width' => 640,
            'height' => 480,
            'hasMenubar' => false,
            'margined' => true,
            'onClosing' => null,
            'resizable' => true,
        ];
    }

    protected function createNativeControl(): CData
    {
        return Window::create(
            $this->getConfig('title'),
            $this->getConfig('width'),
            $this->getConfig('height'),
            $this->getConfig('hasMenubar') ? 1 : 0,
        );
    }

    protected function applyConfig(): void
    {
        Window::setMargined($this->handle, $this->getConfig('margined'));

        // 设置关闭事件
        $onClosing = $this->getConfig('onClosing');
        if ($onClosing === null) {
            // 默认关闭行为
            Window::onClosing($this->handle, static function () {
                App::quit();
                return 1;
            });
        } else {
            Window::onClosing($this->handle, $onClosing);
        }
    }

    protected function canHaveChildren(): bool
    {
        return true;
    }

    protected function buildChildren(): void
    {
        if (count($this->children) === 1) {
            // 单个子组件直接设置
            Window::setChild($this->handle, $this->children[0]->build());
        } elseif (count($this->children) > 1) {
            // 多个子组件自动包装在垂直盒子中
            $vbox = new BoxBuilder('vertical');
            foreach ($this->children as $child) {
                $vbox->addChild($child);
            }
            Window::setChild($this->handle, $vbox->build());
        }
    }

    public function show(): void
    {
        $this->build();
        Control::show($this->handle);
        App::main();
    }

    public function showBuilt(): void
    {
        Control::show($this->handle);
        App::main();
    }

    // 链式配置方法
    public function title(string $title): static
    {
        return $this->setConfig('title', $title);
    }

    public function size(int $width, int $height): static
    {
        return $this->setConfig('width', $width)->setConfig('height', $height);
    }

    public function onClosing(callable $callback): static
    {
        return $this->setConfig('onClosing', $callback);
    }
}
