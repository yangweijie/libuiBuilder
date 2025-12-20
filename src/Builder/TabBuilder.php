<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Tab;

/**
 * 标签页构建器
 */
class TabBuilder extends ComponentBuilder
{
    /** @var array 标签页列表 */
    protected array $tabs = [];

    /**
     * 设置标签页
     *
     * @param array $tabs 标签页数组 ['标签名' => ComponentBuilder]
     * @return $this
     */
    public function tabs(array $tabs): self
    {
        $this->tabs = $tabs;
        return $this;
    }

    /**
     * 添加标签页
     *
     * @param string $name 标签名
     * @param ComponentBuilder $content 内容组件
     * @return $this
     */
    public function addTab(string $name, ComponentBuilder $content): self
    {
        $this->tabs[$name] = $content;
        return $this;
    }

    /**
     * 注册标签页选中事件
     *
     * @param callable $callback 回调函数
     * @return $this
     */
    public function onTabSelected(callable $callback): self
    {
        $this->events['onTabSelected'] = $callback;
        return $this;
    }

    /**
     * 构建标签页组件
     *
     * @return CData 标签页句柄
     */
    protected function buildComponent(): CData
    {
        // 创建标签页
        $this->handle = Tab::create();

        // 添加标签页
        foreach ($this->tabs as $name => $content) {
            echo "[TAB_DEBUG] 构建标签页: {$name}\n";
            $contentHandle = $content->build();
            echo "[TAB_DEBUG] 标签页内容构建完成\n";
            Tab::append($this->handle, $name, $contentHandle);
        }

        // 设置边距
        if (isset($this->config['margined'])) {
            Tab::setMargined($this->handle, 0, $this->config['margined']);
        }

        return $this->handle;
    }

    /**
     * 构建后处理 - 绑定事件
     *
     * @return void
     */
    protected function afterBuild(): void
    {
        // 绑定标签页选中事件
        if (isset($this->events['onTabSelected'])) {
            $callback = $this->events['onTabSelected'];
            Tab::onSelected($this->handle, function($tab) use ($callback) {
                $selectedIndex = Tab::selected($tab);
                $callback($selectedIndex);
            });
        }
    }

    /**
     * 获取组件类型
     *
     * @return string
     */
    public function getType(): string
    {
        return 'tab';
    }

    /**
     * 获取当前选中的标签页索引
     *
     * @return int|null
     */
    public function getSelectedIndex(): ?int
    {
        if ($this->handle) {
            return Tab::selected($this->handle);
        }
        return null;
    }

    /**
     * 设置选中的标签页
     *
     * @param int $index 标签页索引
     * @return $this
     */
    public function setSelectedIndex(int $index): self
    {
        if ($this->handle) {
            Tab::setSelected($this->handle, $index);
        }
        return $this;
    }

    /**
     * 获取标签页数量
     *
     * @return int|null
     */
    public function getTabCount(): ?int
    {
        if ($this->handle) {
            return Tab::numPages($this->handle);
        }
        return count($this->tabs);
    }

    /**
     * 设置标签页边距
     *
     * @param int $page 标签页索引
     * @param bool $margined 是否有边距
     * @return $this
     */
    public function setMargined(int $page, bool $margined): self
    {
        if ($this->handle) {
            Tab::setMargined($this->handle, $page, $margined);
        }
        return $this;
    }
}