<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Area;
use Kingbes\Libui\Draw;
use Kingbes\Libui\DrawFillMode;
use FFI\CData;

class CanvasBuilder extends ComponentBuilder
{
    private array $drawCommands = [];

    public function getDefaultConfig(): array
    {
        return [
            'width' => 400,
            'height' => 300,
            'onDraw' => null,
            'onMouseEvent' => null,
            'onKeyEvent' => null,
            'backgroundColor' => [1.0, 1.0, 1.0, 1.0], // 白色
        ];
    }

    protected function createNativeControl(): CData
    {
        $handler = Area::createHandler(
            [$this, 'onDraw'],
            [$this, 'onMouseEvent'],
            [$this, 'onMouseCrossed'],
            [$this, 'onDragBroken'],
            [$this, 'onKeyEvent']
        );

        return Area::create($handler);
    }

    protected function applyConfig(): void
    {
        // 设置最小尺寸
        Area::setSize($this->handle,
            $this->getConfig('width'),
            $this->getConfig('height')
        );
    }

    // 绘制回调
    public function onDraw(CData $area, CData $params): void
    {
        $context = $params->Context;

        // 清空背景
        $bg = $this->getConfig('backgroundColor');
        $this->fillRect($context, 0, 0,
            $this->getConfig('width'),
            $this->getConfig('height'),
            $bg[0], $bg[1], $bg[2], $bg[3]
        );

        // 执行绘制命令
        foreach ($this->drawCommands as $command) {
            $this->executeDrawCommand($context, $command);
        }

        // 用户自定义绘制
        if ($onDraw = $this->getConfig('onDraw')) {
            $onDraw(new DrawContext($context), $params);
        }
    }

    private function executeDrawCommand(CData $context, array $command): void
    {
        switch ($command['type']) {
            case 'rect':
                $this->drawRect($context, $command);
                break;
            case 'circle':
                $this->drawCircle($context, $command);
                break;
            case 'line':
                $this->drawLine($context, $command);
                break;
            case 'text':
                $this->drawText($context, $command);
                break;
        }
    }

    private function fillRect(CData $context, float $x, float $y, float $w, float $h,
                              float $r, float $g, float $b, float $a): void
    {
        $path = Draw::createPath(DrawFillMode::Winding);
        Draw::createPathFigure($path, $x, $y);
        Draw::pathLineTo($path, $x + $w, $y);
        Draw::pathLineTo($path, $x + $w, $y + $h);
        Draw::pathLineTo($path, $x, $y + $h);
        Draw::pathCloseFigure($path);
        Draw::pathEnd($path);

        $brush = Draw::createSolidBrush($r, $g, $b, $a);
        Draw::fill($context, $path, $brush);
        Draw::freePath($path);
    }

    // 链式API for drawing commands
    public function rect(float $x, float $y, float $w, float $h, array $color = [0, 0, 0, 1]): static
    {
        $this->drawCommands[] = [
            'type' => 'rect',
            'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h,
            'color' => $color
        ];
        return $this;
    }

    public function circle(float $cx, float $cy, float $radius, array $color = [0, 0, 0, 1]): static
    {
        $this->drawCommands[] = [
            'type' => 'circle',
            'cx' => $cx, 'cy' => $cy, 'radius' => $radius,
            'color' => $color
        ];
        return $this;
    }

    public function line(float $x1, float $y1, float $x2, float $y2,
                         array $color = [0, 0, 0, 1], float $width = 1.0): static
    {
        $this->drawCommands[] = [
            'type' => 'line',
            'x1' => $x1, 'y1' => $y1, 'x2' => $x2, 'y2' => $y2,
            'color' => $color, 'width' => $width
        ];
        return $this;
    }

    public function text(string $text, float $x, float $y, array $color = [0, 0, 0, 1]): static
    {
        $this->drawCommands[] = [
            'type' => 'text',
            'text' => $text, 'x' => $x, 'y' => $y,
            'color' => $color
        ];
        return $this;
    }

    public function clear(): static
    {
        $this->drawCommands = [];
        Area::queueRedrawAll($this->handle);
        return $this;
    }

    public function redraw(): static
    {
        Area::queueRedrawAll($this->handle);
        return $this;
    }

    // 事件处理回调
    public function onMouseEvent(CData $area, CData $event): void
    {
        if ($onMouseEvent = $this->getConfig('onMouseEvent')) {
            $onMouseEvent($event);
        }
    }

    public function onKeyEvent(CData $area, CData $event): bool
    {
        if ($onKeyEvent = $this->getConfig('onKeyEvent')) {
            return $onKeyEvent($event);
        }
        return false;
    }

    // 其他必需的回调
    public function onMouseCrossed(CData $area, bool $left): void {}
    public function onDragBroken(CData $area): void {}
}