<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Radio;

/**
 * 单选按钮构建器
 */
class RadioBuilder extends ComponentBuilder
{
    /** @var array 选项列表 */
    protected array $items = [];

    /**
     * 设置选项
     *
     * @param array $items 选项数组
     * @return $this
     */
    public function items(array $items): self
    {
        $this->items = $items;
        return $this;
    }

    /**
     * 添加选项
     *
     * @param string $item 选项文本
     * @return $this
     */
    public function append(string $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * 设置选中项
     *
     * @param int $index 选中项索引
     * @return $this
     */
    public function selected(int $index): self
    {
        $this->config['selected'] = $index;
        return $this;
    }

    /**
     * 绑定状态
     *
     * @param string $stateKey 状态键
     * @return $this
     */
    public function bind(string $stateKey): self
    {
        $this->config['bind'] = $stateKey;
        
        // 如果有状态管理器，自动同步初始值
        if ($this->stateManager && $this->stateManager->has($stateKey)) {
            $selectedValue = $this->stateManager->get($stateKey);
            $index = array_search($selectedValue, $this->items);
            if ($index !== false) {
                $this->config['selected'] = $index;
            }
        }
        
        return $this;
    }

    /**
     * 构建单选按钮组件
     *
     * @return CData 单选按钮句柄
     */
    protected function buildComponent(): CData
    {
        // 创建单选按钮
        $this->handle = Radio::create();

        // 添加选项
        foreach ($this->items as $item) {
            Radio::append($this->handle, $item);
        }

        // 设置选中项
        if (isset($this->config['selected'])) {
            Radio::setSelected($this->handle, $this->config['selected']);
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
        // 绑定选中改变事件
        if (isset($this->events['onChange']) || isset($this->config['bind'])) {
            $callback = $this->events['onChange'] ?? null;
            $stateKey = $this->config['bind'] ?? null;
            $stateManager = $this->stateManager;
            $items = $this->items;
            
            Radio::onSelected($this->handle, function($radio) use ($callback, $stateKey, $stateManager, $items) {
                $index = Radio::selected($radio);
                $value = $items[$index] ?? null;
                
                // 更新状态
                if ($stateKey && $stateManager) {
                    $stateManager->set($stateKey, $value);
                }
                
                // 调用回调
                if ($callback) {
                    if ($stateManager) {
                        $callback($index, $value, $this, $stateManager);
                    } else {
                        $callback($index, $value, $this);
                    }
                }
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
        return 'radio';
    }

    /**
     * 获取选项列表
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * 获取选中项索引
     *
     * @return int|null
     */
    public function getSelected(): ?int
    {
        if ($this->handle) {
            return Radio::selected($this->handle);
        }
        return $this->config['selected'] ?? null;
    }

    /**
     * 获取选中项值
     *
     * @return string|null
     */
    public function getValue(): mixed
    {
        $index = $this->getSelected();
        if ($index !== null && isset($this->items[$index])) {
            return $this->items[$index];
        }
        return null;
    }

    /**
     * 设置选中项值
     *
     * @param mixed $value
     * @return self
     */
    public function setValue(mixed $value): self
    {
        if (is_string($value)) {
            $index = array_search($value, $this->items);
            if ($index !== false) {
                $this->config['selected'] = $index;
                if ($this->handle) {
                    Radio::setSelected($this->handle, $index);
                }
            }
        } elseif (is_int($value)) {
            $this->config['selected'] = $value;
            if ($this->handle) {
                Radio::setSelected($this->handle, $value);
            }
        }
        return $this;
    }
}