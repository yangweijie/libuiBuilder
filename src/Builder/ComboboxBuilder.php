<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Combobox;

/**
 * 组合框构建器
 */
class ComboboxBuilder extends ComponentBuilder
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
    public function addItem(string $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * 设置初始选中项
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
     * 绑定到状态
     *
     * @param string $stateKey 状态键名
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
     * 构建组合框组件
     *
     * @return CData 组合框句柄
     */
    protected function buildComponent(): CData
    {
        // 创建组合框
        $this->handle = Combobox::create();

        // 添加选项
        foreach ($this->items as $item) {
            Combobox::append($this->handle, $item);
        }

        // 设置初始选中
        if (isset($this->config['selected'])) {
            Combobox::setSelected($this->handle, $this->config['selected']);
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
            
            Combobox::onSelected($this->handle, function($combobox) use ($callback, $stateKey, $stateManager, $items) {
                $index = Combobox::selected($combobox);
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
        return 'combobox';
    }

    /**
     * 获取选中项的值
     *
     * @return string|null
     */
    public function getValue(): ?string
    {
        if ($this->handle) {
            $index = Combobox::selected($this->handle);
            return $this->items[$index] ?? null;
        }
        
        if (isset($this->config['selected'])) {
            return $this->items[$this->config['selected']] ?? null;
        }
        
        return null;
    }

    /**
     * 设置选中项（动态更新）
     *
     * @param int $index 选中项索引
     * @return $this
     */
    public function setSelected(int $index): self
    {
        $this->config['selected'] = $index;
        
        if ($this->handle) {
            Combobox::setSelected($this->handle, $index);
        }
        
        // 更新绑定的状态
        if (isset($this->config['bind']) && $this->stateManager) {
            $value = $this->items[$index] ?? null;
            if ($value !== null) {
                $this->stateManager->set($this->config['bind'], $value);
            }
        }
        
        return $this;
    }

    /**
     * 设置组件值（实现ComponentInterface）
     *
     * @param mixed $value
     * @return self
     */
    public function setValue(mixed $value): self
    {
        $this->config['value'] = $value;
        
        // 查找值的索引
        $index = array_search($value, $this->items, true);
        if ($index !== false) {
            $this->config['selected'] = $index;
            
            if ($this->handle) {
                Combobox::setSelected($this->handle, $index);
            }
        }
        
        // 更新绑定的状态
        if (isset($this->config['bind']) && $this->stateManager) {
            $this->stateManager->set($this->config['bind'], $value);
        }
        
        return $this;
    }

    
}
