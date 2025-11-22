<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Separator;
use FFI\CData;

class SeparatorBuilder extends ComponentBuilder
{
    protected function getDefaultConfig(): array
    {
        return [
            'orientation' => 'horizontal', // horizontal, vertical
            'spacing' => 0, // 额外间距
        ];
    }

    protected function createNativeControl(): CData
    {
        $orientation = $this->getConfig('orientation');

        return $orientation === 'vertical'
            ? Separator::createVertical()
            : Separator::createHorizontal();
    }

    protected function applyConfig(): void
    {
        // 分隔符没有特殊配置需要应用
        // 间距通过父容器的padding来控制
    }

    // 分隔符不需要getValue/setValue，但为了接口一致性保留
    public function getValue()
    {
        return null;
    }

    public function setValue($value): void
    {
        // 分隔符没有值可以设置
    }

    // 链式配置方法
    public function horizontal(): static
    {
        return $this->setConfig('orientation', 'horizontal');
    }

    public function vertical(): static
    {
        return $this->setConfig('orientation', 'vertical');
    }

    public function orientation(string $orientation): static
    {
        return $this->setConfig('orientation', $orientation);
    }

    public function spacing(int $spacing): static
    {
        return $this->setConfig('spacing', $spacing);
    }
}