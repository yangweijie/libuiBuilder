<?php

namespace Kingbes\Libui\View\Components;

use FFI;
use FFI\CData;
use Kingbes\Libui\Area;
use Kingbes\Libui\Draw;
use Kingbes\Libui\DrawFillMode;
use Kingbes\Libui\DrawBrushType;
use Kingbes\Libui\DrawLineCap;
use Kingbes\Libui\DrawLineJoin;

/**
 * DrawContext - 绘图上下文封装
 * 
 * 提供便捷的绘图方法，封装原生 libui 绘图 API：
 * - 基础图形（矩形、圆形、线条、椭圆）
 * - 路径绘制（贝塞尔曲线、圆弧）
 * - 文本绘制
 * - 渐变填充
 * - 变换操作
 */
class DrawContext
{
    /** @var CData 绘图上下文句柄 */
    protected CData $ctx;

    public function __construct(CData $ctx)
    {
        $this->ctx = $ctx;
    }

    // ========== 路径操作 ==========

    /**
     * 创建路径
     */
    public function createPath(DrawFillMode $fillMode = DrawFillMode::Winding): CData
    {
        return Area::ffi()->uiDrawNewPath($fillMode->value);
    }

    /**
     * 释放路径
     */
    public function freePath(CData $path): void
    {
        Area::ffi()->uiDrawFreePath($path);
    }

    /**
     * 开始新图形
     */
    public function beginFigure(CData $path, float $x, float $y): void
    {
        Area::ffi()->uiDrawPathNewFigure($path, $x, $y);
    }

    /**
     * 添加直线到路径
     */
    public function lineTo(CData $path, float $x, float $y): void
    {
        Area::ffi()->uiDrawPathLineTo($path, $x, $y);
    }

    /**
     * 添加圆弧到路径
     * 
     * @param float $xCenter 圆心 X
     * @param float $yCenter 圆心 Y
     * @param float $radius 半径
     * @param float $startAngle 起始角度（弧度）
     * @param float $sweep 扫掠角度（弧度）
     * @param bool $negative 是否逆时针
     */
    public function arcTo(CData $path, float $xCenter, float $yCenter, float $radius, float $startAngle, float $sweep, bool $negative = false): void
    {
        Area::ffi()->uiDrawPathArcTo($path, $xCenter, $yCenter, $radius, $startAngle, $sweep, $negative ? 1 : 0);
    }

    /**
     * 添加圆弧（从起点开始）
     */
    public function arc(CData $path, float $xCenter, float $yCenter, float $radius, float $startAngle, float $sweep, bool $negative = false): void
    {
        Area::ffi()->uiDrawPathNewFigureWithArc($path, $xCenter, $yCenter, $radius, $startAngle, $sweep, $negative ? 1 : 0);
    }

    /**
     * 添加贝塞尔曲线
     */
    public function bezierTo(CData $path, float $c1x, float $c1y, float $c2x, float $c2y, float $endX, float $endY): void
    {
        Area::ffi()->uiDrawPathBezierTo($path, $c1x, $c1y, $c2x, $c2y, $endX, $endY);
    }

    /**
     * 关闭图形
     */
    public function closeFigure(CData $path): void
    {
        Area::ffi()->uiDrawPathCloseFigure($path);
    }

    /**
     * 添加矩形到路径
     */
    public function addRect(CData $path, float $x, float $y, float $width, float $height): void
    {
        Area::ffi()->uiDrawPathAddRectangle($path, $x, $y, $width, $height);
    }

    /**
     * 结束路径定义
     */
    public function endPath(CData $path): void
    {
        Area::ffi()->uiDrawPathEnd($path);
    }

    // ========== 笔刷和描边 ==========

    /**
     * 创建实色笔刷
     * 
     * @param array $color [r, g, b, a] 0-1 或 0-255
     */
    public function createSolidBrush(array $color): CData
    {
        [$r, $g, $b, $a] = $this->normalizeColor($color);
        return Draw::createBrush(DrawBrushType::Solid, $r, $g, $b, $a);
    }

    /**
     * 创建线性渐变笔刷
     * 
     * @param float $x0 渐变起点 X
     * @param float $y0 渐变起点 Y
     * @param float $x1 渐变终点 X
     * @param float $y1 渐变终点 Y
     * @param array $stops [[pos, r, g, b, a], ...] 渐变停止点
     */
    public function createLinearGradientBrush(float $x0, float $y0, float $x1, float $y1, array $stops): CData
    {
        $ffi = Area::ffi();
        $numStops = count($stops);
        $cStops = $ffi->new("struct uiDrawBrushGradientStop[$numStops]");
        
        for ($i = 0; $i < $numStops; $i++) {
            $cStops[$i]->Pos = $stops[$i][0];
            [$r, $g, $b, $a] = $this->normalizeColor(array_slice($stops[$i], 1));
            $cStops[$i]->R = $r;
            $cStops[$i]->G = $g;
            $cStops[$i]->B = $b;
            $cStops[$i]->A = $a;
        }

        // allocate a uiDrawBrush and return a pointer
        $brushPtr = FFI::addr($ffi->new('struct uiDrawBrush'));
        $brushPtr[0]->Type = DrawBrushType::LinearGradient->value;
        $brushPtr[0]->X0 = $x0;
        $brushPtr[0]->Y0 = $y0;
        $brushPtr[0]->X1 = $x1;
        $brushPtr[0]->Y1 = $y1;
        $brushPtr[0]->Stops = $ffi->cast("uiDrawBrushGradientStop *", $cStops);
        $brushPtr[0]->NumStops = $numStops;

        return $brushPtr;
    }

    /**
     * 创建径向渐变笔刷
     */
    public function createRadialGradientBrush(float $x0, float $y0, float $outerRadius, array $stops): CData
    {
        $ffi = Area::ffi();
        $numStops = count($stops);
        $cStops = $ffi->new("struct uiDrawBrushGradientStop[$numStops]");
        
        for ($i = 0; $i < $numStops; $i++) {
            $cStops[$i]->Pos = $stops[$i][0];
            [$r, $g, $b, $a] = $this->normalizeColor(array_slice($stops[$i], 1));
            $cStops[$i]->R = $r;
            $cStops[$i]->G = $g;
            $cStops[$i]->B = $b;
            $cStops[$i]->A = $a;
        }

        $brushPtr = FFI::addr($ffi->new('struct uiDrawBrush'));
        $brushPtr[0]->Type = DrawBrushType::RadialGradient->value;
        $brushPtr[0]->X0 = $x0;
        $brushPtr[0]->Y0 = $y0;
        $brushPtr[0]->OuterRadius = $outerRadius;
        $brushPtr[0]->Stops = $ffi->cast("uiDrawBrushGradientStop *", $cStops);
        $brushPtr[0]->NumStops = $numStops;

        return $brushPtr;
     }

    /**
     * 创建描边参数
     */
    public function createStrokeParams(
         float $thickness = 1.0,
        DrawLineCap $cap = DrawLineCap::Flat,
        DrawLineJoin $join = DrawLineJoin::Miter,
        float $miterLimit = 10.0,
        array $dashes = [],
        float $dashPhase = 0.0
    ): CData {
        $ffi = Area::ffi();
        $stroke = FFI::addr($ffi->new('struct uiDrawStrokeParams'));
         $stroke->Thickness = $thickness;
         $stroke->Cap = $cap->value;
         $stroke->Join = $join->value;
         $stroke->MiterLimit = $miterLimit;
         $stroke->DashPhase = $dashPhase;

         $numDashes = count($dashes);
         $stroke->NumDashes = $numDashes;

         if ($numDashes > 0) {
             $cDashes = $ffi->new("double[$numDashes]");
             for ($i = 0; $i < $numDashes; $i++) {
                 $cDashes[$i] = $dashes[$i];
             }
             $stroke->Dashes = $cDashes;
         }

         return $stroke;
     }

     // ========== 填充和描边 ==========

     /**
      * 填充路径
      */
     public function fill(CData $path, CData $brush): void
     {
+        // brush should be a pointer-to-struct
         Area::ffi()->uiDrawFill($this->ctx, $path, $brush);
     }

     /**
      * 描边路径
      */
     public function stroke(CData $path, CData $brush, CData $strokeParams): void
     {
         $ffi = Area::ffi();
+        // strokeParams expected to be pointer
         $ffi->uiDrawStroke($this->ctx, $path, $brush, $strokeParams);
     }

    // ========== 便捷方法 ==========

    /**
     * 填充矩形
     * 
     * @param array $color [r, g, b, a]
     */
    public function fillRect(float $x, float $y, float $width, float $height, array $color = [0, 0, 0, 1]): void
    {
        $path = $this->createPath();
        $this->addRect($path, $x, $y, $width, $height);
        $this->endPath($path);
        
        $ffi = Area::ffi();
        [$r, $g, $b, $a] = $this->normalizeColor($color);
        
        // 创建 brush 结构体
        $brush = $ffi->new("struct uiDrawBrush");
        $brush->Type = DrawBrushType::Solid->value;
        $brush->R = $r;
        $brush->G = $g;
        $brush->B = $b;
        $brush->A = $a;
        
        $ffi->uiDrawFill($this->ctx, $path, FFI::addr($brush));
        $this->freePath($path);
    }

    /**
     * 描边矩形
     */
    public function strokeRect(float $x, float $y, float $width, float $height, array $color = [0, 0, 0, 1], float $thickness = 1.0): void
    {
        $path = $this->createPath();
        $this->addRect($path, $x, $y, $width, $height);
        $this->endPath($path);

        $brush = $this->createSolidBrush($color);
        $stroke = $this->createStrokeParams($thickness);
        $this->stroke($path, $brush, $stroke);
        $this->freePath($path);
    }

    /**
     * 绘制线条
     */
    public function strokeLine(float $x1, float $y1, float $x2, float $y2, array $color = [0, 0, 0, 1], float $thickness = 1.0): void
    {
        $path = $this->createPath();
        $this->beginFigure($path, $x1, $y1);
        $this->lineTo($path, $x2, $y2);
        $this->endPath($path);

        $brush = $this->createSolidBrush($color);
        $stroke = $this->createStrokeParams($thickness);
        $this->stroke($path, $brush, $stroke);
        $this->freePath($path);
    }

    /**
     * 填充圆形
     */
    public function fillCircle(float $cx, float $cy, float $r, array $color = [0, 0, 0, 1]): void
    {
        $path = $this->createPath();
        $this->arc($path, $cx, $cy, $r, 0.0, 2.0 * pi());
        $this->closeFigure($path);
        $this->endPath($path);

        $brush = $this->createSolidBrush($color);
        $this->fill($path, $brush);
        $this->freePath($path);
    }

    /**
     * 描边圆形
     */
    public function strokeCircle(float $cx, float $cy, float $r, array $color = [0, 0, 0, 1], float $thickness = 1.0): void
    {
        $path = $this->createPath();
        $this->arc($path, $cx, $cy, $r, 0.0, 2.0 * pi());
        $this->closeFigure($path);
        $this->endPath($path);

        $brush = $this->createSolidBrush($color);
        $stroke = $this->createStrokeParams($thickness);
        $this->stroke($path, $brush, $stroke);
        $this->freePath($path);
    }

    /**
     * 填充椭圆
     */
    public function fillEllipse(float $cx, float $cy, float $rx, float $ry, array $color = [0, 0, 0, 1]): void
    {
        // 使用路径绘制椭圆（通过缩放变换）
        $path = $this->createPath();
        $this->beginFigure($path, $cx - $rx, $cy);
        $this->bezierTo($path, $cx - $rx, $cy - $ry, $cx + $rx, $cy - $ry, $cx + $rx, $cy);
        $this->bezierTo($path, $cx + $rx, $cy + $ry, $cx - $rx, $cy + $ry, $cx - $rx, $cy);
        $this->endPath($path);

        $brush = $this->createSolidBrush($color);
        $this->fill($path, $brush);
        $this->freePath($path);
    }

    /**
     * 绘制圆角矩形
     */
    public function fillRoundedRect(float $x, float $y, float $width, float $height, float $radius, array $color = [0, 0, 0, 1]): void
    {
        $path = $this->createPath();
        
        // 右上角
        $this->arc($path, $x + $width - $radius, $y + $radius, $radius, -M_PI_2, M_PI_2);
        // 右下角
        $this->arc($path, $x + $width - $radius, $y + $height - $radius, $radius, 0, M_PI_2);
        // 左下角
        $this->arc($path, $x + $radius, $y + $height - $radius, $radius, M_PI_2, M_PI_2);
        // 左上角
        $this->arc($path, $x + $radius, $y + $radius, $radius, M_PI, M_PI_2);
        
        $this->endPath($path);
        $brush = $this->createSolidBrush($color);
        $this->fill($path, $brush);
        $this->freePath($path);
    }

    /**
     * 绘制多边形
     * 
     * @param array $points [[x, y], [x, y], ...]
     */
    public function fillPolygon(array $points, array $color = [0, 0, 0, 1]): void
    {
        if (count($points) < 3) return;

        $path = $this->createPath();
        $this->beginFigure($path, $points[0][0], $points[0][1]);
        
        for ($i = 1; $i < count($points); $i++) {
            $this->lineTo($path, $points[$i][0], $points[$i][1]);
        }
        
        $this->closeFigure($path);
        $this->endPath($path);

        $brush = $this->createSolidBrush($color);
        $this->fill($path, $brush);
        $this->freePath($path);
    }

    // ========== 变换操作 ==========

    /**
     * 保存上下文状态
     */
    public function save(): void
    {
        Area::ffi()->uiDrawSave($this->ctx);
    }

    /**
     * 恢复上下文状态
     */
    public function restore(): void
    {
        Area::ffi()->uiDrawRestore($this->ctx);
    }

    /**
     * 创建变换矩阵
     */
    public function createMatrix(float $m11 = 1, float $m12 = 0, float $m21 = 0, float $m22 = 1, float $M31 = 0, float $M32 = 0): CData
    {
        return Draw::createMatrix($m11, $m12, $m21, $m22, $M31, $M32);
    }

    /**
     * 应用变换矩阵
     */
    public function transform(CData $matrix): void
    {
        Draw::transform($this->ctx, $matrix);
    }

    /**
     * 剪辑路径
     */
    public function clip(CData $path): void
    {
        Draw::clip($this->ctx, $path);
    }

    // ========== 文本绘制 ==========

    /**
     * 获取原生上下文句柄
     */
    public function getRaw(): CData
    {
        return $this->ctx;
    }

    // ========== 辅助方法 ==========

    /**
     * 标准化颜色值（支持 0-1 和 0-255）
     */
    protected function normalizeColor(array $color): array
    {
        $r = $color[0] ?? 0;
        $g = $color[1] ?? 0;
        $b = $color[2] ?? 0;
        $a = $color[3] ?? 1;

        // 检测是否使用 0-255 范围
        if ($r > 1 || $g > 1 || $b > 1) {
            $r = (float)($r / 255);
            $g = (float)($g / 255);
            $b = (float)($b / 255);
        }

        // Alpha 始终假设 0-1
        $a = (float)$a;

        return [$r, $g, $b, $a];
    }
}

