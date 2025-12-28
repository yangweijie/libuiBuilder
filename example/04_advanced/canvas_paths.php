<?php
/**
 * Canvas 路径绘制示例
 * 
 * 演示如何使用路径 API 绘制各种形状：
 * - 直线和折线
 * - 圆弧
 * - 贝塞尔曲线
 * - 星形和多角形
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
    ->size(600, 500)
    ->onDraw(function ($builder, $ctx, $params) {
        $width = $params['AreaWidth'];
        $height = $params['AreaHeight'];

        // 绘制背景网格
        $ctx->save();
        $ctx->fillRect(0, 0, $width, $height, [250, 250, 250, 1.0]);
        for ($x = 0; $x < $width; $x += 30) {
            $ctx->strokeLine($x, 0, $x, $height, [220, 220, 220, 1.0], 0.5);
        }
        for ($y = 0; $y < $height; $y += 30) {
            $ctx->strokeLine(0, $y, $width, $y, [220, 220, 220, 1.0], 0.5);
        }
        $ctx->restore();

        // 1. 绘制箭头
        $ctx->save();
        $arrowPath = $ctx->createPath();
        $ctx->beginFigure($arrowPath, 50, 50);
        $ctx->lineTo($arrowPath, 150, 50);
        $ctx->lineTo($arrowPath, 150, 30);
        $ctx->lineTo($arrowPath, 200, 60);
        $ctx->lineTo($arrowPath, 150, 90);
        $ctx->lineTo($arrowPath, 150, 70);
        $ctx->lineTo($arrowPath, 50, 70);
        $ctx->endPath($arrowPath);
        $ctx->fill($arrowPath, $ctx->createSolidBrush([0.2, 0.6, 0.9, 1.0]));
        $ctx->stroke($arrowPath, $ctx->createSolidBrush([0.1, 0.4, 0.7, 1.0]), $ctx->createStrokeParams(2.0));
        $ctx->freePath($arrowPath);
        $ctx->restore();

        // 2. 绘制圆弧（扇形）
        $ctx->save();
        $arcPath = $ctx->createPath();
        $ctx->beginFigure($arcPath, 100, 180);
        $ctx->arc($arcPath, 100, 180, 50, 0, M_PI_2); // 90度弧
        $ctx->closeFigure($arcPath);
        $ctx->endPath($arcPath);
        $ctx->fill($arcPath, $ctx->createSolidBrush([0.9, 0.6, 0.2, 1.0]));
        $ctx->stroke($arcPath, $ctx->createSolidBrush([0.7, 0.4, 0.1, 1.0]), $ctx->createStrokeParams(2.0));
        $ctx->freePath($arcPath);
        $ctx->restore();

        // 3. 绘制贝塞尔曲线（波浪线）
        $ctx->save();
        $wavePath = $ctx->createPath();
        $ctx->beginFigure($wavePath, 200, 150);
        $ctx->bezierTo($wavePath, 250, 100, 300, 200, 350, 150);
        $ctx->bezierTo($wavePath, 400, 100, 450, 200, 500, 150);
        $ctx->endPath($wavePath);
        $ctx->stroke($wavePath, $ctx->createSolidBrush([0.3, 0.8, 0.3, 1.0]), $ctx->createStrokeParams(4.0));
        $ctx->freePath($wavePath);
        $ctx->restore();

        // 4. 绘制五角星
        $ctx->save();
        $starPath = $ctx->createPath();
        $cx = 150;
        $cy = 350;
        $outerRadius = 50;
        $innerRadius = 20;
        $points = 5;

        for ($i = 0; $i < $points * 2; $i++) {
            $radius = ($i % 2 === 0) ? $outerRadius : $innerRadius;
            $angle = ($i * M_PI) / $points - M_PI_2;
            $x = $cx + cos($angle) * $radius;
            $y = $cy + sin($angle) * $radius;

            if ($i === 0) {
                $ctx->beginFigure($starPath, $x, $y);
            } else {
                $ctx->lineTo($starPath, $x, $y);
            }
        }
        $ctx->closeFigure($starPath);
        $ctx->endPath($starPath);
        $ctx->fill($starPath, $ctx->createSolidBrush([1.0, 0.8, 0.0, 1.0]));
        $ctx->stroke($starPath, $ctx->createSolidBrush([0.8, 0.6, 0.0, 1.0]), $ctx->createStrokeParams(2.0));
        $ctx->freePath($starPath);
        $ctx->restore();

        // 5. 绘制六边形
        $ctx->save();
        $hexPath = $ctx->createPath();
        $cx = 350;
        $cy = 350;
        $radius = 45;
        $sides = 6;

        for ($i = 0; $i < $sides; $i++) {
            $angle = ($i * 2 * M_PI) / $sides;
            $x = $cx + cos($angle) * $radius;
            $y = $cy + sin($angle) * $radius;

            if ($i === 0) {
                $ctx->beginFigure($hexPath, $x, $y);
            } else {
                $ctx->lineTo($hexPath, $x, $y);
            }
        }
        $ctx->closeFigure($hexPath);
        $ctx->endPath($hexPath);
        $ctx->fill($hexPath, $ctx->createSolidBrush([0.6, 0.4, 0.8, 1.0]));
        $ctx->stroke($hexPath, $ctx->createSolidBrush([0.4, 0.2, 0.6, 1.0]), $ctx->createStrokeParams(2.0));
        $ctx->freePath($hexPath);
        $ctx->restore();

        // 6. 绘制虚线
        $ctx->save();
        $dashPath = $ctx->createPath();
        $ctx->beginFigure($dashPath, 450, 150);
        $ctx->lineTo($dashPath, 550, 250);
        $ctx->endPath($dashPath);
        $ctx->stroke($dashPath, $ctx->createSolidBrush([0.9, 0.2, 0.4, 1.0]), $ctx->createStrokeParams(3.0, \Kingbes\Libui\DrawLineCap::Round, \Kingbes\Libui\DrawLineJoin::Round, 10.0, [10, 5], 0));
        $ctx->freePath($dashPath);
        $ctx->restore();

        // 7. 绘制箭头曲线
        $ctx->save();
        $arrowCurvePath = $ctx->createPath();
        $ctx->beginFigure($arrowCurvePath, 480, 150);
        $ctx->bezierTo($arrowCurvePath, 480, 100, 520, 100, 520, 150);
        $ctx->lineTo($arrowCurvePath, 520, 150);
        // 箭头头部
        $ctx->lineTo($arrowCurvePath, 540, 170);
        $ctx->lineTo($arrowCurvePath, 500, 170);
        $ctx->lineTo($arrowCurvePath, 500, 150);
        $ctx->endPath($arrowCurvePath);
        $ctx->fill($arrowCurvePath, $ctx->createSolidBrush([0.3, 0.8, 0.6, 1.0]));
        $ctx->stroke($arrowCurvePath, $ctx->createSolidBrush([0.1, 0.6, 0.4, 1.0]), $ctx->createStrokeParams(2.0));
        $ctx->freePath($arrowCurvePath);
        $ctx->restore();
    });

// 创建窗口
$app = window()
    ->title('Canvas 路径绘制示例')
    ->size(620, 550)
    ->contains([$canvas]);

$app->show();

