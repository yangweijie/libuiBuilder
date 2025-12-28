<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Draw;
use Kingbes\Libui\DrawFillMode;
use FFI\CData;

/**
 * 绘制路径构建器
 * 用于创建复杂的矢量路径，支持矩形、圆形、线条、贝塞尔曲线等
 */
class DrawPathBuilder extends ComponentBuilder
{
    private ?CData $path = null;
    private DrawFillMode $fillMode;
    private bool $closed = false;

    public function getDefaultConfig(): array
    {
        return [
            'fillMode' => DrawFillMode::Winding,
        ];
    }

    protected function createNativeControl(): CData
    {
        $this->fillMode = $this->getConfig('fillMode', DrawFillMode::Winding);
        return Draw::createPath($this->fillMode);
    }

    protected function applyConfig(): void
    {
        // 配置已在 createNativeControl 中处理
    }

    /**
     * 开始一个新路径图形
     */
    public function figure(float $x, float $y): static
    {
        $this->ensurePath();
        Draw::createPathFigure($this->handle, $x, $y);
        return $this;
    }

    /**
     * 添加直线到路径
     */
    public function lineTo(float $x, float $y): static
    {
        $this->ensurePath();
        Draw::pathLineTo($this->handle, $x, $y);
        return $this;
    }

    /**
     * 添加圆弧到路径
     */
    public function arcTo(
        float $xCenter,
        float $yCenter,
        float $radius,
        float $startAngle,
        float $sweep,
        bool $negative = false
    ): static {
        $this->ensurePath();
        Draw::pathArcTo($this->handle, $xCenter, $yCenter, $radius, $startAngle, $sweep, $negative);
        return $this;
    }

    /**
     * 添加贝塞尔曲线到路径
     */
    public function bezierTo(float $c1x, float $c1y, float $c2x, float $c2y, float $endX, float $endY): static
    {
        $this->ensurePath();
        Draw::pathBezierTo($this->handle, $c1x, $c1y, $c2x, $c2y, $endX, $endY);
        return $this;
    }

    /**
     * 关闭当前图形
     */
    public function closeFigure(): static
    {
        $this->ensurePath();
        Draw::pathCloseFigure($this->handle);
        $this->closed = true;
        return $this;
    }

    /**
     * 添加矩形到路径
     */
    public function addRect(float $x, float $y, float $width, float $height): static
    {
        $this->ensurePath();
        Draw::pathAddRectangle($this->handle, $x, $y, $width, $height);
        return $this;
    }

    /**
     * 添加圆形弧（使用快捷方式）
     */
    public function addArc(
        float $xCenter,
        float $yCenter,
        float $radius,
        float $startAngle,
        float $sweep,
        bool $negative = false
    ): static {
        return $this->arcTo($xCenter, $yCenter, $radius, $startAngle, $sweep, $negative);
    }

    /**
     * 添加椭圆弧
     */
    public function ellipseArc(
        float $xCenter,
        float $yCenter,
        float $radius,
        float $startAngle,
        float $sweep
    ): static {
        return $this->arcTo($xCenter, $yCenter, $radius, $startAngle, $sweep);
    }

    /**
     * 结束路径
     */
    public function end(): static
    {
        $this->ensurePath();
        Draw::pathEnd($this->handle);
        return $this;
    }

    /**
     * 绘制矩形快捷方法
     */
    public static function rect(float $x, float $y, float $width, float $height, DrawFillMode $fillMode = DrawFillMode::Winding): static
    {
        return (new static(['fillMode' => $fillMode]))
            ->figure($x, $y)
            ->lineTo($x + $width, $y)
            ->lineTo($x + $width, $y + $height)
            ->lineTo($x, $y + $height)
            ->closeFigure()
            ->end();
    }

    /**
     * 绘制圆形快捷方法
     */
    public static function circle(
        float $cx,
        float $cy,
        float $radius,
        DrawFillMode $fillMode = DrawFillMode::Winding
    ): static {
        return (new static(['fillMode' => $fillMode]))
            ->figure($cx + $radius, $cy)
            ->arcTo($cx, $cy, $radius, 0, M_PI * 2)
            ->closeFigure()
            ->end();
    }

    /**
     * 绘制线条快捷方法
     */
    public static function line(
        float $x1,
        float $y1,
        float $x2,
        float $y2,
        DrawFillMode $fillMode = DrawFillMode::Winding
    ): static {
        return (new static(['fillMode' => $fillMode]))
            ->figure($x1, $y1)
            ->lineTo($x2, $y2)
            ->end();
    }

    /**
     * 获取路径句柄
     */
    public function getPath(): CData
    {
        $this->ensurePath();
        return $this->handle;
    }

    /**
     * 判断路径是否已关闭
     */
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * 确保路径已创建
     */
    private function ensurePath(): void
    {
        if ($this->handle === null) {
            $this->handle = $this->createNativeControl();
        }
    }

    /**
     * 释放路径资源
     */
    public function free(): void
    {
        if ($this->handle !== null) {
            Draw::freePath($this->handle);
            $this->handle = null;
        }
    }

    public function build(): CData
    {
        if ($this->handle === null) {
            $this->handle = $this->createNativeControl();
        }
        return $this->handle;
    }
}
