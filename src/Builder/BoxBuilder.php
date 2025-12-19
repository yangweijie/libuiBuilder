<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Box;

/**
 * 盒子容器构建器
 */
class BoxBuilder extends ComponentBuilder
{
    /** @var array 子组件列表 */
    protected array $children = [];

    /**
     * 构造函数
     *
     * @param string $direction 方向 ('horizontal' 或 'vertical')
     */
    public function __construct(string $direction = 'vertical')
    {
        $this->config['direction'] = $direction;
    }

    /**
     * 设置方向
     *
     * @param string $direction 方向 ('horizontal' 或 'vertical')
     * @return $this
     */
    public function direction(string $direction): self
    {
        $this->config['direction'] = $direction;
        return $this;
    }

    /**
     * 设置内边距
     *
     * @param bool $padded 是否有内边距
     * @return $this
     */
    public function padded(bool $padded): self
    {
        $this->config['padded'] = $padded;
        return $this;
    }

    /**
     * 设置子组件
     *
     * @param array|ComponentBuilder $children 子组件数组或单个组件
     * @return $this
     */
    public function contains($children): self
    {
        if (is_array($children)) {
            $this->children = $children;
        } else {
            $this->children = [$children];
        }
        return $this;
    }

    /**
     * 添加子组件
     *
     * @param ComponentBuilder $child 子组件
     * @param bool $stretchy 是否可拉伸
     * @return $this
     */
    public function append(ComponentBuilder $child, bool $stretchy = false): self
    {
        $this->children[] = [
            'component' => $child,
            'stretchy' => $stretchy
        ];
        return $this;
    }

    /**
     * 构建盒子
     *
     * @return CData 盒子句柄
     */
    public function build(): CData
    {
        $direction = $this->config['direction'] ?? 'vertical';
        
        // 创建盒子
        if ($direction === 'horizontal') {
            $this->handle = Box::newHorizontalBox();
        } else {
            $this->handle = Box::newVerticalBox();
        }

        // 设置内边距
        if (isset($this->config['padded'])) {
            Box::setPadded($this->handle, $this->config['padded']);
        }

        // 添加子组件
        foreach ($this->children as $child) {
            if ($child instanceof ComponentBuilder) {
                // 简化用法：直接传递组件
                $childHandle = $child->build();
                Box::append($this->handle, $childHandle, false);
            } elseif (is_array($child) && isset($child['component'])) {
                // 完整用法：包含 stretchy 设置
                $childHandle = $child['component']->build();
                $stretchy = $child['stretchy'] ?? false;
                Box::append($this->handle, $childHandle, $stretchy);
            }
        }

        // 注册到状态管理器
        if ($this->id && $this->stateManager) {
            $this->stateManager->registerComponent($this->id, $this);
        }

        return $this->handle;
    }

    /**
     * 获取组件类型
     *
     * @return string
     */
    public function getType(): string
    {
        return 'box';
    }

    /**
     * 获取子组件
     *
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}
