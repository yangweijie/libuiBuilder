<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Draw;
use Kingbes\Libui\DrawBrushType;
use FFI\CData;

/**
 * 画刷构建器
 * 用于创建各种类型的画刷（实色、线性渐变、径向渐变）
 */
class DrawBrushBuilder extends ComponentBuilder
{
    private array $gradientStops = [];
    private ?CData $brush = null;

    public function getDefaultConfig(): array
    {
        return [
            'type' => DrawBrushType::Solid,
            'color' => [0.0, 0.0, 0.0, 1.0], // RGBA
            'x0' => 0.0,
            'y0' => 0.0,
            'x1' => 0.0,
            'y1' => 0.0,
            'outerRadius' => 0.0,
        ];
    }

    protected function createNativeControl(): CData
    {
        $config = $this->config;
        $this->brush = Draw::createBrush(
            $config['type'],
            $config['color'][0] ?? 0.0,
            $config['color'][1] ?? 0.0,
            $config['color'][2] ?? 0.0,
            $config['color'][3] ?? 1.0,
            $config['x0'] ?? 0.0,
            $config['y0'] ?? 0.0,
            $config['x1'] ?? 0.0,
            $config['y1'] ?? 0.0,
            $config['outerRadius'] ?? 0.0
        );
        return $this->brush;
    }

    protected function applyConfig(): void
    {
        // 配置已在 createNativeControl 中处理
    }

    /**
     * 设置画刷类型为实色
     */
    public function solid(float $r = 0.0, float $g = 0.0, float $b = 0.0, float $a = 1.0): static
    {
        $this->setConfig('type', DrawBrushType::Solid);
        $this->setConfig('color', [$r, $g, $b, $a]);
        $this->brush = null; // 标记需要重新创建
        return $this;
    }

    /**
     * 设置画刷类型为线性渐变
     */
    public function linearGradient(
        float $x0,
        float $y0,
        float $x1,
        float $y1,
        array $color = [0.0, 0.0, 0.0, 1.0]
    ): static {
        $this->setConfig('type', DrawBrushType::LinearGradient);
        $this->setConfig('color', $color);
        $this->setConfig('x0', $x0);
        $this->setConfig('y0', $y0);
        $this->setConfig('x1', $x1);
        $this->setConfig('y1', $y1);
        $this->brush = null;
        return $this;
    }

    /**
     * 设置画刷类型为径向渐变
     */
    public function radialGradient(
        float $x0,
        float $y0,
        float $x1,
        float $y1,
        float $outerRadius,
        array $color = [0.0, 0.0, 0.0, 1.0]
    ): static {
        $this->setConfig('type', DrawBrushType::RadialGradient);
        $this->setConfig('color', $color);
        $this->setConfig('x0', $x0);
        $this->setConfig('y0', $y0);
        $this->setConfig('x1', $x1);
        $this->setConfig('y1', $y1);
        $this->setConfig('outerRadius', $outerRadius);
        $this->brush = null;
        return $this;
    }

    /**
     * 添加渐变停止点
     */
    public function addStop(float $pos, float $r, float $g, float $b, float $a = 1.0): static
    {
        $this->gradientStops[] = [
            'pos' => $pos,
            'color' => [$r, $g, $b, $a]
        ];
        return $this;
    }

    /**
     * 设置颜色（快捷方法）
     */
    public function color(float $r, float $g, float $b, float $a = 1.0): static
    {
        return $this->solid($r, $g, $b, $a);
    }

    /**
     * 设置红色通道
     */
    public function fillRed(float $value = 1.0): static
    {
        $color = $this->getConfig('color', [0.0, 0.0, 0.0, 1.0]);
        $color[0] = max(0.0, min(1.0, $value));
        $this->setConfig('color', $color);
        $this->brush = null;
        return $this;
    }

    /**
     * 设置绿色通道
     */
    public function fillGreen(float $value = 1.0): static
    {
        $color = $this->getConfig('color', [0.0, 0.0, 0.0, 1.0]);
        $color[1] = max(0.0, min(1.0, $value));
        $this->setConfig('color', $color);
        $this->brush = null;
        return $this;
    }

    /**
     * 设置蓝色通道
     */
    public function fillBlue(float $value = 1.0): static
    {
        $color = $this->getConfig('color', [0.0, 0.0, 0.0, 1.0]);
        $color[2] = max(0.0, min(1.0, $value));
        $this->setConfig('color', $color);
        $this->brush = null;
        return $this;
    }

    /**
     * 设置透明度
     */
    public function fillAlpha(float $value = 1.0): static
    {
        $color = $this->getConfig('color', [0.0, 0.0, 0.0, 1.0]);
        $color[3] = max(0.0, min(1.0, $value));
        $this->setConfig('color', $color);
        $this->brush = null;
        return $this;
    }

    /**
     * 设置十六进制颜色
     */
    public function hexColor(string $hex): static
    {
        $hex = ltrim($hex, '#');
        $len = strlen($hex);

        if ($len === 3) {
            $r = hexdec($hex[0] . $hex[0]) / 255;
            $g = hexdec($hex[1] . $hex[1]) / 255;
            $b = hexdec($hex[2] . $hex[2]) / 255;
            $a = 1.0;
        } elseif ($len === 6) {
            $r = hexdec(substr($hex, 0, 2)) / 255;
            $g = hexdec(substr($hex, 2, 2)) / 255;
            $b = hexdec(substr($hex, 4, 2)) / 255;
            $a = 1.0;
        } elseif ($len === 8) {
            $r = hexdec(substr($hex, 0, 2)) / 255;
            $g = hexdec(substr($hex, 2, 2)) / 255;
            $b = hexdec(substr($hex, 4, 2)) / 255;
            $a = hexdec(substr($hex, 6, 2)) / 255;
        } else {
            return $this;
        }

        return $this->solid($r, $g, $b, $a);
    }

    /**
     * 获取画刷句柄
     */
    public function getBrush(): CData
    {
        $this->ensureBrush();
        return $this->brush;
    }

    /**
     * 确保画刷已创建
     */
    private function ensureBrush(): void
    {
        if ($this->brush === null) {
            $this->handle = $this->createNativeControl();
        }
    }

    /**
     * 创建实色画刷快捷方法
     */
    public static function solidColor(float $r = 0.0, float $g = 0.0, float $b = 0.0, float $a = 1.0): static
    {
        return (new static())->solid($r, $g, $b, $a);
    }

    /**
     * 创建黑色画刷快捷方法
     */
    public static function black(): static
    {
        return (new static())->solid(0.0, 0.0, 0.0, 1.0);
    }

    /**
     * 创建白色画刷快捷方法
     */
    public static function white(): static
    {
        return (new static())->solid(1.0, 1.0, 1.0, 1.0);
    }

    /**
     * 创建红色画刷快捷方法
     */
    public static function red(): static
    {
        return (new static())->solid(1.0, 0.0, 0.0, 1.0);
    }

    /**
     * 创建绿色画刷快捷方法
     */
    public static function green(): static
    {
        return (new static())->solid(0.0, 1.0, 0.0, 1.0);
    }

    /**
     * 创建蓝色画刷快捷方法
     */
    public static function blue(): static
    {
        return (new static())->solid(0.0, 0.0, 1.0, 1.0);
    }

    public function build(): CData
    {
        $this->ensureBrush();
        return $this->brush;
    }
}
