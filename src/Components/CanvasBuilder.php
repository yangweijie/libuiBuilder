<?php

namespace Kingbes\Libui\View\Components;

use FFI\CData;
use Kingbes\Libui\Area;
use Kingbes\Libui\View\Validation\ComponentBuilder;

/**
 * CanvasBuilder - 画布组件构建器
 * 
 * 提供基于 libui 的绘图功能，支持：
 * - 基础图形绘制（矩形、圆形、线条）
 * - 路径绘制
 * - 文本绘制
 * - 渐变和变换
 * - 鼠标/键盘交互
 */
class CanvasBuilder extends ComponentBuilder
{
    /** @var callable 绘制回调 */
    private $onDraw = null;

    /** @var callable 键盘事件回调 */
    private $onKeyEvent = null;

    /** @var callable 鼠标事件回调 */
    private $onMouseEvent = null;

    /** @var callable 鼠标跨域回调 */
    private $onMouseCrossed = null;

    /** @var callable 拖拽中断回调 */
    private $onDragBroken = null;

    /** @var CData 保持处理程序引用防止 GC */
    private ?CData $areaHandler = null;

    /** @var callable 保持绘制回调引用防止 GC */
    private $onDrawCallback = null;

    /** @var array 静态存储所有闭包引用防止 GC */
    private static array $closureRefs = [];

    /**
     * 创建 CanvasBuilder 实例
     */
    public static function create(array $config = []): static
    {
        return new static($config);
    }

    public function getDefaultConfig(): array
    {
        return [
            'onDraw' => null,
            'onKeyEvent' => null,
            'onMouseEvent' => null,
            'onMouseCrossed' => null,
            'onDragBroken' => null,
            'width' => null,
            'height' => null,
            'scroll' => false,
            'scrollWidth' => 800,
            'scrollHeight' => 600,
        ];
    }

    protected function createNativeControl(): CData
    {
        // 保存回调引用到实例变量，防止 GC
        $onDraw = $this->onDraw;
        $onKeyEvent = $this->onKeyEvent;
        $onMouseEvent = $this->onMouseEvent;
        $onMouseCrossed = $this->onMouseCrossed;
        $onDragBroken = $this->onDragBroken;
        
        // 保存 $this 引用供闭包使用
        $builder = $this;

        // 绘制回调 - 使用局部变量而非 $this
        $c_draw = function ($h, $area, $params) use ($onDraw, $builder) {
            try {
                // 安全获取参数，处理 params 可能是数组或 CData 的情况
                $areaWidth = 0.0;
                $areaHeight = 0.0;
                $clipX = 0.0;
                $clipY = 0.0;
                $clipWidth = 0.0;
                $clipHeight = 0.0;
                $context = null;

                if (is_array($params)) {
                    $areaWidth = $params['AreaWidth'] ?? 0.0;
                    $areaHeight = $params['AreaHeight'] ?? 0.0;
                    $clipX = $params['ClipX'] ?? 0.0;
                    $clipY = $params['ClipY'] ?? 0.0;
                    $clipWidth = $params['ClipWidth'] ?? 0.0;
                    $clipHeight = $params['ClipHeight'] ?? 0.0;
                    $context = $params['Context'] ?? null;
                } elseif ($params instanceof CData) {
                    // FFI CData 处理：直接访问成员，如果返回 CData 则尝试提取值
                    $aw = $params->AreaWidth;
                    $areaWidth = is_numeric($aw) ? (float)$aw : 0.0;
                    
                    $ah = $params->AreaHeight;
                    $areaHeight = is_numeric($ah) ? (float)$ah : 0.0;
                    
                    $cx = $params->ClipX;
                    $clipX = is_numeric($cx) ? (float)$cx : 0.0;
                    
                    $cy = $params->ClipY;
                    $clipY = is_numeric($cy) ? (float)$cy : 0.0;
                    
                    $cw = $params->ClipWidth;
                    $clipWidth = is_numeric($cw) ? (float)$cw : 0.0;
                    
                    $ch = $params->ClipHeight;
                    $clipHeight = is_numeric($ch) ? (float)$ch : 0.0;
                    
                    $context = $params->Context ?? null;
                }

                $drawParams = [
                    'AreaWidth' => $areaWidth,
                    'AreaHeight' => $areaHeight,
                    'ClipX' => $clipX,
                    'ClipY' => $clipY,
                    'ClipWidth' => $clipWidth,
                    'ClipHeight' => $clipHeight,
                    'raw' => $params,
                ];

                $drawCtx = null;
                if ($context) {
                    $drawCtx = new DrawContext($context);
                }

                if ($onDraw && is_callable($onDraw)) {
                    $onDraw($builder, $drawCtx, $drawParams);
                }
            } catch (\Throwable $e) {
                error_log("CanvasBuilder::onDraw error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            }
        };

        // 保存绘制回调引用防止 GC
        $this->onDrawCallback = $c_draw;

        // 只在有回调时才创建处理程序
        if ($onKeyEvent || $onMouseEvent || $onMouseCrossed || $onDragBroken) {
            // 键盘事件回调
            $c_keyEvent = function ($h, $area, $keyEvent) use ($onKeyEvent, $builder) {
                if ($onKeyEvent && is_callable($onKeyEvent)) {
                    return $onKeyEvent($builder, $area, $keyEvent);
                }
                return 1;
            };

            // 鼠标事件回调
            $c_mouseEvent = function ($h, $area, $mouseEvent) use ($onMouseEvent, $builder) {
                if ($onMouseEvent && is_callable($onMouseEvent)) {
                    $onMouseEvent($builder, $area, $mouseEvent);
                }
            };

            // 鼠标跨域回调
            $c_mouseCrossed = function ($h, $area, $left) use ($onMouseCrossed, $builder) {
                if ($onMouseCrossed && is_callable($onMouseCrossed)) {
                    $onMouseCrossed($builder, $area, $left);
                }
            };

            // 拖拽中断回调
            $c_dragBroken = function ($h, $area) use ($onDragBroken, $builder) {
                if ($onDragBroken && is_callable($onDragBroken)) {
                    $onDragBroken($builder, $area);
                }
            };

            // 保存所有闭包引用到静态数组防止 GC
            self::$closureRefs[] = [$c_draw, $c_keyEvent, $c_mouseEvent, $c_mouseCrossed, $c_dragBroken];

            $ah = Area::handler($c_draw, $c_keyEvent, $c_mouseEvent, $c_mouseCrossed, $c_dragBroken);
        } else {
            // 只保存绘制闭包
            self::$closureRefs[] = [$c_draw];

            $ah = Area::handler($c_draw);
        }

        // 保存处理程序引用防止 GC
        $this->areaHandler = $ah;

        if ($this->getConfig('scroll')) {
            $w = (int) $this->getConfig('scrollWidth');
            $h = (int) $this->getConfig('scrollHeight');
            if ($w <= 0) $w = 400;
            if ($h <= 0) $h = 300;
            $a = Area::createScroll($ah, $w, $h);
        } else {
            $a = Area::create($ah);
        }

        return $a;
    }

    protected function applyConfig(): void
    {
        // Nothing else to wire at the moment; size handled in createNativeControl
    }

    // 链式方法

    /**
     * 设置绘制回调
     * 
     * @param callable $handler function(CanvasBuilder $builder, DrawContext $ctx, array $params)
     * @return $this
     */
    public function onDraw(callable $handler): static
    {
        $this->onDraw = $handler;
        return $this;
    }

    /**
     * 设置键盘事件回调
     * 
     * @param callable $handler function(CanvasBuilder $builder, $area, $keyEvent): bool
     * @return $this
     */
    public function onKey(callable $handler): static
    {
        $this->onKeyEvent = $handler;
        return $this;
    }

    /**
     * 设置鼠标事件回调
     * 
     * @param callable $handler function(CanvasBuilder $builder, $area, $mouseEvent)
     * @return $this
     */
    public function onMouse(callable $handler): static
    {
        $this->onMouseEvent = $handler;
        return $this;
    }

    /**
     * 设置鼠标跨域回调（鼠标离开/进入画布）
     * 
     * @param callable $handler function(CanvasBuilder $builder, $area, int $left)
     * @return $this
     */
    public function onMouseCrossed(callable $handler): static
    {
        $this->onMouseCrossed = $handler;
        return $this;
    }

    /**
     * 设置拖拽中断回调
     * 
     * @param callable $handler function(CanvasBuilder $builder, $area)
     * @return $this
     */
    public function onDragBroken(callable $handler): static
    {
        $this->onDragBroken = $handler;
        return $this;
    }

    /**
     * 设置画布尺寸（像素）
     */
    public function size(int $width, int $height): static
    {
        $this->setConfig('width', $width);
        $this->setConfig('height', $height);
        if ($this->handle) {
            try {
                Area::setSize($this->handle, $width, $height);
            } catch (\Throwable $e) {
                error_log("CanvasBuilder::size error: " . $e->getMessage());
            }
        }
        return $this;
    }

    /**
     * 设置为可滚动画布
     */
    public function scrollable(int $width, int $height): static
    {
        $this->setConfig('scroll', true);
        $this->setConfig('scrollWidth', $width);
        $this->setConfig('scrollHeight', $height);
        return $this;
    }

    public function queueRedraw(): void
    {
        if ($this->handle) {
            try {
                Area::queueRedraw($this->handle);
            } catch (\Throwable $e) {
                error_log("CanvasBuilder::queueRedraw error: " . $e->getMessage());
            }
        }
    }

    // getValue/setValue not particularly meaningful for canvas but keep stubs
    public function getValue()
    {
        return $this->getConfig('value');
    }

    public function setValue($value): void
    {
        $this->setConfig('value', $value);
    }
}

