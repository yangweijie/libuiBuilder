<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\Area;
use Kingbes\Libui\DrawContext;
use Kingbes\Libui\View\Validation\ComponentBuilder;

/**
 * 图表组件构建器 - 示例扩展组件
 */
class ChartBuilder extends ComponentBuilder
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    public function getDefaultConfig(): array
    {
        return [
            'title' => '图表',
            'width' => 400,
            'height' => 300,
            'type' => 'line',
            'data' => [],
            'color' => [0.2, 0.4, 0.8, 1.0], // 默认蓝色
        ];
    }

    protected function createNativeControl(): \FFI\CData
    {
        // 创建绘图区域处理程序
        $onDraw = function ($h, $area, $params) {
            try {
                $areaWidth = $params['AreaWidth'] ?? 0.0;
                $areaHeight = $params['AreaHeight'] ?? 0.0;
                
                // 创建绘图上下文
                $drawCtx = new DrawContext($params['Context'] ?? null);
                
                if (!$drawCtx) {
                    return;
                }
                
                // 绘制图表
                $this->drawChart($drawCtx, $areaWidth, $areaHeight);
                
            } catch (\Throwable $e) {
                error_log("ChartBuilder::onDraw error: " . $e->getMessage());
            }
        };
        
        // 创建 Area 处理程序
        $ah = Area::handler($onDraw);
        
        // 创建绘图区域
        $w = (int) $this->getConfig('width');
        $h = (int) $this->getConfig('height');
        if ($w <= 0) $w = 400;
        if ($h <= 0) $h = 300;
        
        return Area::create($ah);
    }

    /**
     * 绘制图表
     */
    private function drawChart(DrawContext $ctx, float $width, float $height): void
    {
        $type = $this->getConfig('type');
        $data = $this->getConfig('data');
        $color = $this->getConfig('color');
        
        if (empty($data)) {
            // 绘制网格背景
            $this->drawGrid($ctx, $width, $height);
            return;
        }
        
        switch ($type) {
            case 'line':
                $this->drawLineChart($ctx, $width, $height, $data, $color);
                break;
            case 'bar':
                $this->drawBarChart($ctx, $width, $height, $data, $color);
                break;
            case 'pie':
                $this->drawPieChart($ctx, $width, $height, $data, $color);
                break;
            default:
                $this->drawLineChart($ctx, $width, $height, $data, $color);
        }
    }

    /**
     * 绘制网格背景
     */
    private function drawGrid(DrawContext $ctx, float $width, float $height): void
    {
        // 绘制浅灰色背景
        $ctx->fillRect(0, 0, $width, $height, [0.95, 0.95, 0.95, 1.0]);
        
        // 绘制网格线
        $ctx->setLineStyle(1.0); // 1像素线宽
        $ctx->setStrokeColor([0.8, 0.8, 0.8, 1.0]); // 灰色
        
        // 垂直线
        for ($x = 0; $x <= $width; $x += 50) {
            $ctx->strokeLine($x, 0, $x, $height);
        }
        
        // 水平线
        for ($y = 0; $y <= $height; $y += 50) {
            $ctx->strokeLine(0, $y, $width, $y);
        }
    }

    /**
     * 绘制折线图
     */
    private function drawLineChart(DrawContext $ctx, float $width, float $height, array $data, array $color): void
    {
        // 绘制网格背景
        $this->drawGrid($ctx, $width, $height);
        
        if (empty($data)) {
            return;
        }
        
        // 计算坐标范围
        $minValue = min($data);
        $maxValue = max($data);
        $valueRange = $maxValue - $minValue;
        
        if ($valueRange == 0) {
            $valueRange = 1; // 防止除零
        }
        
        // 绘制坐标轴
        $ctx->setStrokeColor([0.2, 0.2, 0.2, 1.0]); // 深灰色
        $ctx->setLineStyle(2.0); // 2像素线宽
        
        // X轴
        $ctx->strokeLine(50, $height - 50, $width - 50, $height - 50);
        // Y轴
        $ctx->strokeLine(50, 50, 50, $height - 50);
        
        // 绘制折线
        $ctx->setStrokeColor($color);
        $ctx->setLineStyle(2.0);
        
        $pointCount = count($data);
        $chartWidth = $width - 100; // 减去边距
        $chartHeight = $height - 100; // 减去边距
        
        $prevX = 50;
        $prevY = $height - 50 - (($data[0] - $minValue) / $valueRange) * $chartHeight;
        
        // 绘制折线
        for ($i = 0; $i < $pointCount; $i++) {
            $x = 50 + ($i / ($pointCount - 1)) * $chartWidth;
            $y = $height - 50 - (($data[$i] - $minValue) / $valueRange) * $chartHeight;
            
            if ($i > 0) {
                $ctx->strokeLine($prevX, $prevY, $x, $y);
            }
            
            // 绘制数据点
            $ctx->fillCircle($x, $y, 3, $color);
            
            $prevX = $x;
            $prevY = $y;
        }
        
        // 绘制坐标轴标签
        $ctx->setStrokeColor([0.1, 0.1, 0.1, 1.0]); // 深灰色
        $ctx->setFontSize(12);
        
        // Y轴标签
        for ($i = 0; $i <= 5; $i++) {
            $y = 50 + ($i / 5) * $chartHeight;
            $value = $maxValue - (($i / 5) * $valueRange);
            $label = number_format($value, 0);
            $ctx->drawText(10, $y - 10, $label);
        }
        
        // X轴标签
        for ($i = 0; $i < $pointCount; $i++) {
            $x = 50 + ($i / ($pointCount - 1)) * $chartWidth;
            $label = $i + 1;
            $ctx->drawText($x - 10, $height - 30, $label);
        }
    }

    /**
     * 绘制柱状图
     */
    private function drawBarChart(DrawContext $ctx, float $width, float $height, array $data, array $color): void
    {
        // 绘制网格背景
        $this->drawGrid($ctx, $width, $height);
        
        if (empty($data)) {
            return;
        }
        
        // 计算坐标范围
        $minValue = min($data);
        $maxValue = max($data);
        $valueRange = $maxValue - $minValue;
        
        if ($valueRange == 0) {
            $valueRange = 1; // 防止除零
        }
        
        // 绘制坐标轴
        $ctx->setStrokeColor([0.2, 0.2, 0.2, 1.0]); // 深灰色
        $ctx->setLineStyle(2.0); // 2像素线宽
        
        // X轴
        $ctx->strokeLine(50, $height - 50, $width - 50, $height - 50);
        // Y轴
        $ctx->strokeLine(50, 50, 50, $height - 50);
        
        // 绘制柱状图
        $pointCount = count($data);
        $chartWidth = $width - 100; // 减去边距
        $chartHeight = $height - 100; // 减去边距
        
        $barWidth = $chartWidth / $pointCount * 0.8; // 柱子宽度
        $barSpacing = $chartWidth / $pointCount * 0.2; // 柱子间距
        
        for ($i = 0; $i < $pointCount; $i++) {
            $x = 50 + ($i * ($barWidth + $barSpacing));
            $barHeight = (($data[$i] - $minValue) / $valueRange) * $chartHeight;
            $y = $height - 50 - $barHeight;
            
            // 绘制柱子
            $ctx->fillRect($x, $y, $barWidth, $barHeight, $color);
            
            // 绘制边框
            $ctx->setStrokeColor([0.1, 0.1, 0.1, 1.0]);
            $ctx->setLineStyle(1.0);
            $ctx->strokeRect($x, $y, $barWidth, $barHeight, [0.1, 0.1, 0.1, 1.0], 1.0);
        }
    }

    /**
     * 绘制饼图
     */
    private function drawPieChart(DrawContext $ctx, float $width, float $height, array $data, array $color): void
    {
        // 绘制网格背景
        $this->drawGrid($ctx, $width, $height);
        
        if (empty($data)) {
            return;
        }
        
        $total = array_sum($data);
        if ($total == 0) {
            return;
        }
        
        $centerX = $width / 2;
        $centerY = $height / 2;
        $radius = min($width, $height) / 4;
        
        $startAngle = 0;
        $colors = [
            [1.0, 0.2, 0.2, 1.0], // 红色
            [0.2, 1.0, 0.2, 1.0], // 绿色
            [0.2, 0.2, 1.0, 1.0], // 蓝色
            [1.0, 1.0, 0.2, 1.0], // 黄色
            [1.0, 0.2, 1.0, 1.0], // 紫色
        ];
        
        foreach ($data as $i => $value) {
            $angle = ($value / $total) * 360;
            $endAngle = $startAngle + $angle;
            
            $color = $colors[$i % count($colors)];
            
            // 绘制扇形
            $this->fillPie($ctx, $centerX, $centerY, $radius, $startAngle, $endAngle, $color);
            
            // 绘制边框
            $ctx->setStrokeColor([0.1, 0.1, 0.1, 1.0]);
            $ctx->setLineStyle(1.0);
            $this->strokePie($ctx, $centerX, $centerY, $radius, $startAngle, $endAngle, [0.1, 0.1, 0.1, 1.0], 1.0);
            
            $startAngle = $endAngle;
        }
    }

    /**
     * 填充扇形
     */
    private function fillPie(DrawContext $ctx, float $cx, float $cy, float $radius, float $startAngle, float $endAngle, array $color): void
    {
        $path = $ctx->createPath();
        
        // 绘制扇形路径
        $ctx->beginFigure($path, $cx, $cy);
        $ctx->arc($path, $cx, $cy, $radius, deg2rad($startAngle), deg2rad($endAngle - $startAngle));
        $ctx->lineTo($path, $cx, $cy);
        $ctx->closeFigure($path);
        $ctx->endPath($path);
        
        // 填充扇形
        $brush = $ctx->createSolidBrush($color);
        $ctx->fill($path, $brush);
        $ctx->freePath($path);
    }

    /**
     * 描边扇形
     */
    private function strokePie(DrawContext $ctx, float $cx, float $cy, float $radius, float $startAngle, float $endAngle, array $color, float $thickness = 1.0): void
    {
        $path = $ctx->createPath();
        
        // 绘制扇形路径
        $ctx->beginFigure($path, $cx, $cy);
        $ctx->arc($path, $cx, $cy, $radius, deg2rad($startAngle), deg2rad($endAngle - $startAngle));
        $ctx->lineTo($path, $cx, $cy);
        $ctx->closeFigure($path);
        $ctx->endPath($path);
        
        // 描边扇形
        $brush = $ctx->createSolidBrush($color);
        $stroke = $ctx->createStrokeParams($thickness);
        $ctx->stroke($path, $brush, $stroke);
        $ctx->freePath($path);
    }

    /**
     * 绘制文本
     */
    private function drawText(DrawContext $ctx, float $x, float $y, string $text, array $color = [0, 0, 0, 1], float $size = 12.0): void
    {
        // 注意：libui 的 DrawContext 没有直接的 drawText 方法
        // 这里我们暂时不实现文本绘制功能
        // 在实际应用中，可以使用 createTextLayout 和 drawTextLayout 方法
    }

    protected function applyConfig(): void
    {
        // 应用配置
    }

    protected function canHaveChildren(): bool
    {
        return false;
    }

    // 图表相关方法
    public function title(string $title): static
    {
        return $this->setConfig('title', $title);
    }

    public function type(string $type): static
    {
        return $this->setConfig('type', $type);
    }

    public function data(array $data): static
    {
        return $this->setConfig('data', $data);
    }

    public function color(array $color): static
    {
        return $this->setConfig('color', $color);
    }
}