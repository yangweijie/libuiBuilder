<?php
/**
 * Canvas 基础绘图示例
 *
 * 演示如何使用 CanvasBuilder 和 DrawContext 绘制基础图形：
 * - 矩形（填充和描边）
 * - 圆形和椭圆
 * - 线条和多边形
 * - 圆角矩形
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Components\CanvasBuilder;
use Kingbes\Libui\View\Components\DrawContext;
use Kingbes\Libui\View\State\StateManager;

App::init();
$state = StateManager::instance();

// 记录方块位置
$state->set('shapes', [
    'rectX' => 50,
    'rectY' => 50,
    'circleX' => 250,
    'circleY' => 150,
]);

// 创建画布
$canvas = CanvasBuilder::create()
    ->scrollable(500, 400)
    ->onDraw(function ($builder, $ctx, $params) use ($state) {
        $shapes = $state->get('shapes');
        $width = $params['AreaWidth'];
        $height = $params['AreaHeight'];

        // 1. 填充矩形（蓝色）
        $ctx->fillRect(50, 50, 150, 80, [0.2, 0.4, 0.8, 1.0]);

        // 2. 描边矩形（红色边框）
        $ctx->strokeRect(250, 50, 150, 80, [1.0, 0.2, 0.2, 1.0], 3.0);

        // 3. 填充圆形（绿色）
        $ctx->fillCircle($shapes['circleX'], $shapes['circleY'], 50, [0.2, 0.8, 0.4, 1.0]);

        // 4. 描边圆形（紫色边框）
        $ctx->strokeCircle(100, 200, 40, [0.6, 0.2, 0.8, 1.0], 2.0);

        // 5. 线条（橙色）
        $ctx->strokeLine(300, 200, 450, 280, [1.0, 0.6, 0.0, 1.0], 4.0);

        // 6. 填充椭圆
        $ctx->fillEllipse(400, 150, 60, 40, [0.9, 0.7, 0.1, 1.0]);

        // 7. 圆角矩形
        $ctx->fillRoundedRect(50, 280, 120, 60, 15, [0.3, 0.7, 0.9, 1.0]);

        // 8. 多边形（黄色三角形）
        $ctx->fillPolygon([
            [400, 280],
            [450, 350],
            [350, 350],
        ], [1.0, 0.9, 0.2, 1.0]);

        // 9. 绘制网格背景
        $ctx->save();
        $ctx->fillRect(0, 0, $width, $height, [245, 245, 245, 1.0]);
        for ($x = 0; $x < $width; $x += 50) {
            $ctx->strokeLine($x, 0, $x, $height, [200, 200, 200, 1.0], 0.5);
        }
        for ($y = 0; $y < $height; $y += 50) {
            $ctx->strokeLine(0, $y, $width, $y, [200, 200, 200, 1.0], 0.5);
        }
        $ctx->restore();
    });

// 创建窗口
$app = window()
    ->title('Canvas 基础绘图示例')
    ->size(699, 480)
    ->contains([$canvas]);

// 创建窗口
$app = window()
    ->title('Canvas 基础绘图示例')
    ->size(700, 500)
    ->contains([$canvas]);

$app->show();
