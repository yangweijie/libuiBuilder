<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Draw;
use FFI\CData;

/**
 * 矩阵变换构建器
 * 用于创建和操作二维变换矩阵
 */
class DrawMatrixBuilder extends ComponentBuilder
{
    private ?CData $matrix = null;

    public function getDefaultConfig(): array
    {
        return [
            'm11' => 1.0,
            'm12' => 0.0,
            'm21' => 0.0,
            'm22' => 1.0,
            'm31' => 0.0,
            'm32' => 0.0,
        ];
    }

    protected function createNativeControl(): CData
    {
        $config = $this->config;
        $this->matrix = Draw::createMatrix(
            $config['m11'],
            $config['m12'],
            $config['m21'],
            $config['m22'],
            $config['m31'],
            $config['m32']
        );
        return $this->matrix;
    }

    protected function applyConfig(): void
    {
        // 配置已在 createNativeControl 中处理
    }

    /**
     * 设置为单位矩阵
     */
    public function setIdentity(): static
    {
        $this->setConfig('m11', 1.0);
        $this->setConfig('m12', 0.0);
        $this->setConfig('m21', 0.0);
        $this->setConfig('m22', 1.0);
        $this->setConfig('m31', 0.0);
        $this->setConfig('m32', 0.0);
        $this->matrix = null;
        return $this;
    }

    /**
     * 平移变换
     */
    public function transformTranslate(float $x, float $y): static
    {
        $this->ensureMatrix();
        Draw::matrixTranslate($this->matrix, $x, $y);
        $this->updateFromNative();
        return $this;
    }

    /**
     * 缩放变换
     */
    public function transformScale(float $xCenter, float $yCenter, float $x, float $y): static
    {
        $this->ensureMatrix();
        Draw::matrixScale($this->matrix, $xCenter, $yCenter, $x, $y);
        $this->updateFromNative();
        return $this;
    }

    /**
     * 等比缩放
     */
    public function transformScaleUniform(float $xCenter, float $yCenter, float $factor): static
    {
        return $this->transformScale($xCenter, $yCenter, $factor, $factor);
    }

    /**
     * 旋转变换
     */
    public function transformRotate(float $x, float $y, float $amount): static
    {
        $this->ensureMatrix();
        Draw::matrixRotate($this->matrix, $x, $y, $amount);
        $this->updateFromNative();
        return $this;
    }

    /**
     * 倾斜变换
     */
    public function transformSkew(float $x, float $y, float $xAmount, float $yAmount): static
    {
        $this->ensureMatrix();
        Draw::matrixSkew($this->matrix, $x, $y, $xAmount, $yAmount);
        $this->updateFromNative();
        return $this;
    }

    /**
     * 水平倾斜
     */
    public function transformSkewX(float $x, float $y, float $amount): static
    {
        return $this->transformSkew($x, $y, $amount, 0.0);
    }

    /**
     * 垂直倾斜
     */
    public function transformSkewY(float $x, float $y, float $amount): static
    {
        return $this->transformSkew($x, $y, 0.0, $amount);
    }

    /**
     * 矩阵乘法
     */
    public function transformMultiply(CData $other): static
    {
        $this->ensureMatrix();
        Draw::matrixMultiply($this->matrix, $other);
        $this->updateFromNative();
        return $this;
    }

    /**
     * 矩阵求逆
     */
    public function invert(): static
    {
        $this->ensureMatrix();
        $result = Draw::matrixInvert($this->matrix);
        if ($result) {
            $this->updateFromNative();
        }
        return $this;
    }

    /**
     * 检查矩阵是否可逆
     */
    public function isInvertible(): bool
    {
        $this->ensureMatrix();
        return Draw::matrixInvertible($this->matrix);
    }

    /**
     * 水平镜像
     */
    public function flipHorizontal(float $xCenter = 0): static
    {
        return $this->scale($xCenter, 0, -1, 1);
    }

    /**
     * 垂直镜像
     */
    public function flipVertical(float $yCenter = 0): static
    {
        return $this->scale(0, $yCenter, 1, -1);
    }

    /**
     * 水平平移
     */
    public function translateX(float $x): static
    {
        return $this->translate($x, 0);
    }

    /**
     * 垂直平移
     */
    public function translateY(float $y): static
    {
        return $this->translate(0, $y);
    }

    /**
     * 水平缩放
     */
    public function scaleX(float $xCenter, float $x): static
    {
        return $this->scale($xCenter, 0, $x, 1);
    }

    /**
     * 垂直缩放
     */
    public function scaleY(float $yCenter, float $y): static
    {
        return $this->scale(0, $yCenter, 1, $y);
    }

    /**
     * 获取矩阵元素
     */
    public function get(string $key): float
    {
        return $this->getConfig($key, 0.0);
    }

    /**
     * 设置矩阵元素
     */
    public function set(string $key, float $value): static
    {
        $this->setConfig($key, $value);
        $this->matrix = null;
        return $this;
    }

    /**
     * 获取矩阵句柄
     */
    public function getMatrix(): CData
    {
        $this->ensureMatrix();
        return $this->matrix;
    }

    /**
     * 确保矩阵已创建
     */
    private function ensureMatrix(): void
    {
        if ($this->matrix === null) {
            $this->handle = $this->createNativeControl();
        }
    }

    /**
     * 从原生对象更新配置
     */
    private function updateFromNative(): void
    {
        if ($this->matrix !== null) {
            $this->setConfig('m11', $this->matrix->M11);
            $this->setConfig('m12', $this->matrix->M12);
            $this->setConfig('m21', $this->matrix->M21);
            $this->setConfig('m22', $this->matrix->M22);
            $this->setConfig('m31', $this->matrix->M31);
            $this->setConfig('m32', $this->matrix->M32);
        }
    }

    /**
     * 创建单位矩阵快捷方法
     */
    public static function identity(): static
    {
        return (new static())->identity();
    }

    /**
     * 创建平移矩阵快捷方法
     */
    public static function translate(float $x, float $y): static
    {
        return (new static())->identity()->translate($x, $y);
    }

    /**
     * 创建缩放矩阵快捷方法
     */
    public static function scale(float $xCenter, float $yCenter, float $x, float $y): static
    {
        return (new static())->identity()->scale($xCenter, $yCenter, $x, $y);
    }

    /**
     * 创建旋转矩阵快捷方法
     */
    public static function rotate(float $x, float $y, float $amount): static
    {
        return (new static())->identity()->rotate($x, $y, $amount);
    }

    public function build(): CData
    {
        $this->ensureMatrix();
        return $this->matrix;
    }
}
