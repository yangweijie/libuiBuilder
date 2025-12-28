<?php
/**
 * Canvas 渐变和复杂图形示例
 * 
 * 演示如何使用 DrawContext 绘制：
 * - 线性渐变
 * - 径向渐变
 * - 贝塞尔曲线
 * - 复杂路径组合
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Components\CanvasBuilder;
use Kingbes\Libui\View\Components\DrawContext;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 创建画布
$canvas = CanvasBuilder::create()
    ->size(650, 550)
    ->onDraw(function ($builder, $ctx, $params) {
        $width = $params['AreaWidth'];
        $height = $params['AreaHeight'];

        // 1. 线性渐变背景
        $linearGradient = $ctx->createLinearGradientBrush(
            0, 0,           // 起点
            650, 0,         // 终点
            [
                [0.0, 0.2, 0.4, 0.8, 1.0],  // 0% - 深蓝色
                [0.4, 0.2, 0.6, 1.0, 1.0],  // 50% - 紫色
                [0.6, 0.3, 0.8, 1.0, 1.0],  // 100% - 粉色
            ]
        );
        $path = $ctx->createPath();
        $ctx->addRect($path, 0, 0, $width, 150);
        $ctx->endPath($path);
        $ctx->fill($path, $linearGradient);
        $ctx->freePath($path);

        // 2. 径向渐变圆形
        $radialGradient = $ctx->createRadialGradientBrush(
            150, 300, 80,  // 中心点和外圆半径
            [
                [0.0, 1.0, 0.8, 0.2, 1.0],  // 中心 - 金色
                [0.5, 1.0, 0.5, 0.0, 1.0],  // 中间 - 橙色
                [1.0, 0.8, 0.2, 0.0, 1.0],  // 边缘 - 深橙色
            ]
        );
        $ctx->fillCircle(150, 300, 80, [1.0, 1.0, 1.0, 0.0]); // 先画白色背景
        $path = $ctx->createPath();
        $ctx->arc($path, 150, 300, 80, 0, 2 * M_PI);
        $ctx->endPath($path);
        $ctx->fill($path, $radialGradient);
        $ctx->freePath($path);

        // 3. 贝塞尔曲线绘制波浪形
        $ctx->save();
        $wavePath = $ctx->createPath();
        $ctx->beginFigure($wavePath, 250, 250);
        $ctx->bezierTo($wavePath, 300, 200, 350, 300, 400, 250);
        $ctx->bezierTo($wavePath, 450, 200, 500, 300, 550, 250);
        $ctx->lineTo($wavePath, 650, 250);
        $ctx->lineTo($wavePath, 650, 350);
        $ctx->lineTo($wavePath, 250, 350);
        $ctx->endPath($wavePath);
        $ctx->fill($wavePath, $ctx->createSolidBrush([0.2, 0.8, 0.9, 0.8]));
        $ctx->stroke($wavePath, $ctx->createSolidBrush([0.1, 0.5, 0.6, 1.0]), $ctx->createStrokeParams(2.0));
        $ctx->freePath($wavePath);
        $ctx->restore();

        // 4. 绘制心形图案
        $ctx->save();
        $heartPath = $ctx->createPath();
        $cx = 450;
        $cy = 300;
        $size = 40;
        $ctx->beginFigure($heartPath, $cx, $cy + $size);
        $ctx->bezierTo($heartPath, $cx - $size, $cy, $cx - $size, $cy - $size, $cx, $cy - $size);
        $ctx->bezierTo($heartPath, $cx + $size, $cy - $size, $cx + $size, $cy, $cx, $cy + $size);
        $ctx->endPath($heartPath);
        $ctx->fill($heartPath, $ctx->createSolidBrush([0.9, 0.2, 0.3, 1.0]));
        $ctx->stroke($heartPath, $ctx->createSolidBrush([0.7, 0.1, 0.2, 1.0]), $ctx->createStrokeParams(2.0));
        $ctx->freePath($heartPath);
        $ctx->restore();

        // 5. 彩虹渐变条
        $rainbowY = 400;
        $rainbowHeight = 40;
        $colors = [
            [1.0, 0.0, 0.0, 1.0],  // 红
            [1.0, 0.5, 0.0, 1.0],  // 橙
            [1.0, 1.0, 0.0, 1.0],  // 黄
            [0.0, 0.8, 0.0, 1.0],  // 绿
            [0.0, 0.0, 1.0, 1.0],  // 蓝
            [0.5, 0.0, 0.8, 1.0],  // 紫
        ];
        $stripWidth = 650 / count($colors);
        foreach ($colors as $i => $color) {
            $ctx->fillRect($i * $stripWidth, $rainbowY, $stripWidth, $rainbowHeight, $color);
        }

        // 6. 渐变叠加模式示例
        $ctx->save();
        // 绘制半透明渐变覆盖层
        $overlayGradient = $ctx->createLinearGradientBrush(
            0, 380, 650, 420,
            [
                [0.0, 1.0, 1.0, 1.0, 0.3],
                [1.0, 1.0, 0.0, 1.0, 0.3],
            ]
        );
        $overlayPath = $ctx->createPath();
        $ctx->addRect($overlayPath, 0, 380, 650, 40);
        $ctx->endPath($overlayPath);
        $ctx->fill($overlayPath, $overlayGradient);
        $ctx->freePath($overlayPath);
        $ctx->restore();

        // 7. 网格背景
        $ctx->save();
        $ctx->fillRect(0, 150, $width, $height - 150, [245, 245, 245, 1.0]);
        for ($x = 0; $x < $width; $x += 30) {
            $ctx->strokeLine($x, 150, $x, $height, [220, 220, 220, 1.0], 0.5);
        }
        for ($y = 150; $y < $height; $y += 30) {
            $ctx->strokeLine(0, $y, $width, $y, [220, 220, 220, 1.0], 0.5);
        }
        $ctx->restore();
    });

// 创建窗口
$app = window()
    ->title('Canvas 渐变和复杂图形示例')
    ->size(660, 600)
    ->contains([$canvas]);

$app->show();