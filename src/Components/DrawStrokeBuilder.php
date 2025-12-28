<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Draw;
use Kingbes\Libui\DrawLineCap;
use Kingbes\Libui\DrawLineJoin;
use FFI\CData;

/**
 * 描边参数构建器
 * 用于配置线条描边的样式（线帽、连接方式、宽度、虚线等）
 */
class DrawStrokeBuilder extends ComponentBuilder
{
    private ?CData $strokeParams = null;

    public function getDefaultConfig(): array
    {
        return [
            'cap' => DrawLineCap::Flat,
            'join' => DrawLineJoin::Miter,
            'join1' => DrawLineJoin::Miter,
            'thickness' => 1.0,
            'miterLimit' => 10.0,
            'dashes' => [],
            'dashPhase' => 0.0,
        ];
    }

    protected function createNativeControl(): CData
    {
        $config = $this->config;
        $dashes = $config['dashes'] ?? [];
        $numDashes = count($dashes);

        $this->strokeParams = Draw::createStrokeParams(
            $config['cap'],
            $config['join'],
            $config['join1'],
            $config['thickness'],
            $config['miterLimit'],
            $numDashes,
            $config['dashPhase'],
            ...$dashes
        );

        return $this->strokeParams;
    }

    protected function applyConfig(): void
    {
        // 配置已在 createNativeControl 中处理
    }

    /**
     * 设置线帽类型为平头
     */
    public function capFlat(): static
    {
        $this->setConfig('cap', DrawLineCap::Flat);
        $this->strokeParams = null;
        return $this;
    }

    /**
     * 设置线帽类型为圆头
     */
    public function capRound(): static
    {
        $this->setConfig('cap', DrawLineCap::Round);
        $this->strokeParams = null;
        return $this;
    }

    /**
     * 设置线帽类型为方头
     */
    public function capSquare(): static
    {
        $this->setConfig('cap', DrawLineCap::Square);
        $this->strokeParams = null;
        return $this;
    }

    /**
     * 设置连接类型为尖角
     */
    public function joinMiter(): static
    {
        $this->setConfig('join', DrawLineJoin::Miter);
        $this->strokeParams = null;
        return $this;
    }

    /**
     * 设置连接类型为圆角
     */
    public function joinRound(): static
    {
        $this->setConfig('join', DrawLineJoin::Round);
        $this->strokeParams = null;
        return $this;
    }

    /**
     * 设置连接类型为斜角
     */
    public function joinBevel(): static
    {
        $this->setConfig('join', DrawLineJoin::Bevel);
        $this->strokeParams = null;
        return $this;
    }

    /**
     * 设置描边宽度
     */
    public function thickness(float $value): static
    {
        $this->setConfig('thickness', max(0.0, $value));
        $this->strokeParams = null;
        return $this;
    }

    /**
     * 设置斜接限制
     */
    public function miterLimit(float $value): static
    {
        $this->setConfig('miterLimit', max(0.0, $value));
        $this->strokeParams = null;
        return $this;
    }

    /**
     * 设置虚线模式
     */
    public function dashed(array $dashes, float $phase = 0.0): static
    {
        $this->setConfig('dashes', array_map(static fn($d) => max(0.0, $d), $dashes));
        $this->setConfig('dashPhase', max(0.0, $phase));
        $this->strokeParams = null;
        return $this;
    }

    /**
     * 设置实线（无虚线）
     */
    public function solid(): static
    {
        $this->setConfig('dashes', []);
        $this->setConfig('dashPhase', 0.0);
        $this->strokeParams = null;
        return $this;
    }

    /**
     * 设置经典的虚线模式 [5, 5]
     */
    public function dashedClassic(): static
    {
        return $this->dashed([5.0, 5.0]);
    }

    /**
     * 设置点划线模式 [10, 5, 2, 5]
     */
    public function dashDot(): static
    {
        return $this->dashed([10.0, 5.0, 2.0, 5.0]);
    }

    /**
     * 设置双点划线模式 [10, 5, 2, 5, 2, 5]
     */
    public function dashDotDot(): static
    {
        return $this->dashed([10.0, 5.0, 2.0, 5.0, 2.0, 5.0]);
    }

    /**
     * 设置线帽类型（快捷方法）
     */
    public function cap($cap): static
    {
        if ($cap instanceof DrawLineCap) {
            $this->setConfig('cap', $cap);
        } elseif (is_string($cap)) {
            $map = [
                'flat' => DrawLineCap::Flat,
                'round' => DrawLineCap::Round,
                'square' => DrawLineCap::Square,
            ];
            if (isset($map[$cap])) {
                $this->setConfig('cap', $map[$cap]);
            }
        }
        $this->strokeParams = null;
        return $this;
    }

    /**
     * 设置连接类型（快捷方法）
     */
    public function join($join): static
    {
        if ($join instanceof DrawLineJoin) {
            $this->setConfig('join', $join);
        } elseif (is_string($join)) {
            $map = [
                'miter' => DrawLineJoin::Miter,
                'round' => DrawLineJoin::Round,
                'bevel' => DrawLineJoin::Bevel,
            ];
            if (isset($map[$join])) {
                $this->setConfig('join', $map[$join]);
            }
        }
        $this->strokeParams = null;
        return $this;
    }

    /**
     * 获取描边参数句柄
     */
    public function getStrokeParams(): CData
    {
        $this->ensureStrokeParams();
        return $this->strokeParams;
    }

    /**
     * 确保描边参数已创建
     */
    private function ensureStrokeParams(): void
    {
        if ($this->strokeParams === null) {
            $this->handle = $this->createNativeControl();
        }
    }

    /**
     * 创建默认描边参数快捷方法
     */
    public static function default(): static
    {
        return (new static())
            ->capRound()
            ->joinRound()
            ->thickness(1.0);
    }

    /**
     * 创建细线描边快捷方法
     */
    public static function thin(): static
    {
        return (new static())
            ->capFlat()
            ->joinMiter()
            ->thickness(0.5);
    }

    /**
     * 创建粗线描边快捷方法
     */
    public static function thick(): static
    {
        return (new static())
            ->capSquare()
            ->joinBevel()
            ->thickness(3.0);
    }

    public function build(): CData
    {
        $this->ensureStrokeParams();
        return $this->strokeParams;
    }
}
