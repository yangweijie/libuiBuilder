<?php

namespace Kingbes\Libui\View\Components;

use FFI\CData;
// 绘制上下文包装类 - 更友好的API
class DrawContext
{
    private CData $context;

    public function __construct(CData $context)
    {
        $this->context = $context;
    }

    public function fillRect(float $x, float $y, float $w, float $h, array $color): void
    {
        // 实现矩形填充
    }

    public function strokeRect(float $x, float $y, float $w, float $h, array $color, float $width = 1.0): void
    {
        // 实现矩形描边
    }

    public function fillCircle(float $cx, float $cy, float $radius, array $color): void
    {
        // 实现圆形填充
    }

    public function drawText(string $text, float $x, float $y, array $color): void
    {
        // 实现文本绘制
    }
}