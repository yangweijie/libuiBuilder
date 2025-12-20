<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Checkbox;

/**
 * 复选框构建器
 */
class CheckboxBuilder extends ComponentBuilder
{
    /**
     * 设置复选框文本
     *
     * @param string $text 文本
     * @return $this
     */
    public function text(string $text): self
    {
        $this->config['text'] = $text;
        return $this;
    }

    /**
     * 设置初始选中状态
     *
     * @param bool $checked 是否选中
     * @return $this
     */
    public function checked(bool $checked): self
    {
        $this->config['checked'] = $checked;
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
            $this->config['checked'] = $this->stateManager->get($stateKey);
        }
        
        return $this;
    }

    /**
     * 构建复选框组件
     *
     * @return CData 复选框句柄
     */
    protected function buildComponent(): CData
    {
        $text = $this->config['text'] ?? '';
        
        // 创建复选框
        $this->handle = Checkbox::create($text);

        // 设置初始选中状态
        if (isset($this->config['checked'])) {
            Checkbox::setChecked($this->handle, $this->config['checked']);
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
        // 绑定状态改变事件
        if (isset($this->events['onChange']) || isset($this->config['bind'])) {
            $callback = $this->events['onChange'] ?? null;
            $stateKey = $this->config['bind'] ?? null;
            $stateManager = $this->stateManager;
            
            Checkbox::onToggled($this->handle, function($checkbox) use ($callback, $stateKey, $stateManager) {
                $checked = Checkbox::checked($checkbox);
                
                // 更新状态
                if ($stateKey && $stateManager) {
                    $stateManager->set($stateKey, $checked);
                }
                
                // 调用回调
                if ($callback) {
                    if ($stateManager) {
                        $callback($checked, $this, $stateManager);
                    } else {
                        $callback($checked, $this);
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
        return 'checkbox';
    }

    /**
     * 获取复选框状态
     *
     * @return bool
     */
    public function getValue(): bool
    {
        if ($this->handle) {
            return Checkbox::checked($this->handle);
        }
        return $this->config['checked'] ?? false;
    }

    /**
     * 设置复选框状态（动态更新）
     *
     * @param bool $checked
     * @return $this
     */
    public function setChecked(bool $checked): self
    {
        $this->config['checked'] = $checked;
        
        if ($this->handle) {
            Checkbox::setChecked($this->handle, $checked);
        }
        
        // 更新绑定的状态
        if (isset($this->config['bind']) && $this->stateManager) {
            $this->stateManager->set($this->config['bind'], $checked);
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
        $checked = (bool)$value;
        $this->config['value'] = $checked;
        $this->config['checked'] = $checked;
        
        if ($this->handle) {
            Checkbox::setChecked($this->handle, $checked);
        }
        
        // 更新绑定的状态
        if (isset($this->config['bind']) && $this->stateManager) {
            $this->stateManager->set($this->config['bind'], $checked);
        }
        
        return $this;
    }

    
}
