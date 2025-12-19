<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Group;

/**
 * 组容器构建器
 */
class GroupBuilder extends ComponentBuilder
{
    /** @var ComponentBuilder|null 子组件 */
    protected ?ComponentBuilder $child = null;

    /**
     * 设置标题
     *
     * @param string $title 标题
     * @return $this
     */
    public function title(string $title): self
    {
        $this->config['title'] = $title;
        return $this;
    }

    /**
     * 设置内边距
     *
     * @param bool $margined 是否有内边距
     * @return $this
     */
    public function margined(bool $margined): self
    {
        $this->config['margined'] = $margined;
        return $this;
    }

    /**
     * 设置子组件
     *
     * @param ComponentBuilder $child 子组件
     * @return $this
     */
    public function contains(ComponentBuilder $child): self
    {
        $this->child = $child;
        return $this;
    }

    /**
     * 构建组容器
     *
     * @return CData 组容器句柄
     */
    public function build(): CData
    {
        $title = $this->config['title'] ?? '';

        // 创建组容器
        $this->handle = Group::create($title);

        // 设置内边距
        if (isset($this->config['margined'])) {
            Group::setMargined($this->handle, $this->config['margined']);
        }

        // 设置子组件
        if ($this->child) {
            $childHandle = $this->child->build();
            Group::setChild($this->handle, $childHandle);
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
        return 'group';
    }

    /**
     * 获取子组件
     *
     * @return ComponentBuilder|null
     */
    public function getChild(): ?ComponentBuilder
    {
        return $this->child;
    }
}
