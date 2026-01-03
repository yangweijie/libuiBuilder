<?php

namespace Kingbes\Libui\View\Components;

use FFI\CData;
use Kingbes\Libui\App;
use Kingbes\Libui\Control;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Enums\WindowPosition;
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

    /**
     * 设置窗口位置（绝对坐标）
     */
    public function position(int $x, int $y): static
    {
        $this->build();
        Window::setPosition($this->handle, $x, $y);
        return $this;
    }

    /**
     * 设置窗口位置到屏幕特定位置
     */
    public function positionOnScreen(WindowPosition $position): static
    {
        $this->build();
        $screenWidth = Builder::screenWidth();
        $screenHeight = Builder::screenHeight();
        $width = $this->getConfig('width');
        $height = $this->getConfig('height');

        [$x, $y] = $this->calculatePosition($position, $screenWidth, $screenHeight, $width, $height);
        Window::setPosition($this->handle, $x, $y);
        return $this;
    }

    /**
     * 快捷方法：居中显示
     */
    public function center(): static
    {
        return $this->positionOnScreen(WindowPosition::CENTER);
    }

    /**
     * 快捷方法：左上角
     */
    public function topLeft(): static
    {
        return $this->positionOnScreen(WindowPosition::TOP_LEFT);
    }

    /**
     * 快捷方法：上居中
     */
    public function topCenter(): static
    {
        return $this->positionOnScreen(WindowPosition::TOP_CENTER);
    }

    /**
     * 快捷方法：右上角
     */
    public function topRight(): static
    {
        return $this->positionOnScreen(WindowPosition::TOP_RIGHT);
    }

    /**
     * 快捷方法：左中
     */
    public function centerLeft(): static
    {
        return $this->positionOnScreen(WindowPosition::CENTER_LEFT);
    }

    /**
     * 快捷方法：右中
     */
    public function centerRight(): static
    {
        return $this->positionOnScreen(WindowPosition::CENTER_RIGHT);
    }

    /**
     * 快捷方法：左下角
     */
    public function bottomLeft(): static
    {
        return $this->positionOnScreen(WindowPosition::BOTTOM_LEFT);
    }

    /**
     * 快捷方法：下居中
     */
    public function bottomCenter(): static
    {
        return $this->positionOnScreen(WindowPosition::BOTTOM_CENTER);
    }

    /**
     * 快捷方法：右下角
     */
    public function bottomRight(): static
    {
        return $this->positionOnScreen(WindowPosition::BOTTOM_RIGHT);
    }

    /**
     * 计算位置
     */
    private function calculatePosition(WindowPosition $position, int $containerWidth, int $containerHeight, int $windowWidth, int $windowHeight): array
    {
        return match($position) {
            WindowPosition::TOP_LEFT => [0, 0],
            WindowPosition::TOP_CENTER => [($containerWidth - $windowWidth) / 2, 0],
            WindowPosition::TOP_RIGHT => [$containerWidth - $windowWidth, 0],
            WindowPosition::CENTER_LEFT => [0, ($containerHeight - $windowHeight) / 2],
            WindowPosition::CENTER => [($containerWidth - $windowWidth) / 2, ($containerHeight - $windowHeight) / 2],
            WindowPosition::CENTER_RIGHT => [$containerWidth - $windowWidth, ($containerHeight - $windowHeight) / 2],
            WindowPosition::BOTTOM_LEFT => [0, $containerHeight - $windowHeight],
            WindowPosition::BOTTOM_CENTER => [($containerWidth - $windowWidth) / 2, $containerHeight - $windowHeight],
            WindowPosition::BOTTOM_RIGHT => [$containerWidth - $windowWidth, $containerHeight - $windowHeight],
        };
    }
}
