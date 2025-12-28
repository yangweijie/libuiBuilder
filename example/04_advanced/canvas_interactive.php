<?php
/**
 * Canvas 交互式绘图示例
 * 
 * 演示如何实现鼠标拖拽交互：
 * - 鼠标按下检测
 * - 拖拽移动
 * - 鼠标释放
 * - 实时重绘
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Components\CanvasBuilder;
use Kingbes\Libui\View\Components\DrawContext;
use Kingbes\Libui\View\State\StateManager;

App::init();
$state = StateManager::instance();

// 初始化状态
$state->set('boxX', 100);
$state->set('boxY', 100);
$state->set('boxWidth', 120);
$state->set('boxHeight', 80);
$state->set('isDragging', false);
$state->set('dragOffsetX', 0);
$state->set('dragOffsetY', 0);
$state->set('mouseX', 0);
$state->set('mouseY', 0);

// 创建画布
$canvas = CanvasBuilder::create()
    ->size(600, 450)
    ->onDraw(function ($builder, $ctx, $params) use ($state) {
        $boxX = $state->get('boxX');
        $boxY = $state->get('boxY');
        $boxWidth = $state->get('boxWidth');
        $boxHeight = $state->get('boxHeight');
        $mouseX = $state->get('mouseX');
        $mouseY = $state->get('mouseY');
        $isDragging = $state->get('isDragging');

        // 绘制背景
        $ctx->fillRect(0, 0, 600, 450, [250, 250, 250, 1.0]);

        // 检测鼠标是否在方块内
        $isHovering = 
            $mouseX >= $boxX && $mouseX <= $boxX + $boxWidth &&
            $mouseY >= $boxY && $mouseY <= $boxY + $boxHeight;

        // 根据状态选择颜色
        if ($isDragging) {
            $color = [0.9, 0.3, 0.3, 1.0]; // 拖拽中 - 红色
        } elseif ($isHovering) {
            $color = [0.3, 0.7, 0.3, 1.0]; // 悬停 - 绿色
        } else {
            $color = [0.3, 0.5, 0.9, 1.0]; // 默认 - 蓝色
        }

        // 绘制可拖拽方块
        $ctx->fillRoundedRect($boxX, $boxY, $boxWidth, $boxHeight, 10, $color);
        $ctx->strokeRect($boxX, $boxY, $boxWidth, $boxHeight, [50, 50, 50, 1.0], 2.0);

        // 绘制阴影效果
        $ctx->fillRoundedRect($boxX + 5, $boxY + 5, $boxWidth, $boxHeight, 10, [0, 0, 0, 0.1]);

        // 绘制提示文本
        $ctx->fillRect($boxX + 10, $boxY + $boxHeight + 10, 200, 30, [240, 240, 240, 1.0]);
        
        // 绘制状态信息
        $statusText = sprintf("位置: (%d, %d)", (int)$boxX, (int)$boxY);
        $statusColor = $isHovering || $isDragging ? [0.2, 0.2, 0.2, 1.0] : [150, 150, 150, 1.0];
        $ctx->strokeRect($boxX + 10, $boxY + $boxHeight + 10, 200, 30, $statusColor, 1.0);
    })
    ->onMouse(function ($builder, $area, $mouseEvent) use ($state) {
        $boxX = $state->get('boxX');
        $boxY = $state->get('boxY');
        $boxWidth = $state->get('boxWidth');
        $boxHeight = $state->get('boxHeight');

        $x = $mouseEvent->X;
        $y = $mouseEvent->Y;
        $down = $mouseEvent->Down;
        $held = $mouseEvent->Held1To64;

        $state->set('mouseX', $x);
        $state->set('mouseY', $y);

        // 检测是否在方块内
        $inBox = 
            $x >= $boxX && $x <= $boxX + $boxWidth &&
            $y >= $boxY && $y <= $boxY + $boxHeight;

        if ($down && $inBox && !$state->get('isDragging')) {
            // 开始拖拽
            $state->set('isDragging', true);
            $state->set('dragOffsetX', $x - $boxX);
            $state->set('dragOffsetY', $y - $boxY);
        }

        if ($held && $state->get('isDragging')) {
            // 拖拽中 - 更新位置
            $newX = $x - $state->get('dragOffsetX');
            $newY = $y - $state->get('dragOffsetY');
            
            // 边界检查
            $newX = max(0, min($newX, 600 - $boxWidth));
            $newY = max(0, min($newY, 450 - $boxHeight));
            
            $state->set('boxX', $newX);
            $state->set('boxY', $newY);
        }

        if (!$held && $state->get('isDragging')) {
            // 结束拖拽
            $state->set('isDragging', false);
        }

        // 触发重绘
        $builder->queueRedraw();
    });

// 创建窗口
$app = window()
    ->title('Canvas 交互式拖拽示例')
    ->size(620, 500)
    ->contains([$canvas]);

$app->show();