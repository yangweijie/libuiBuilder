<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\ProgressBar;

/**
 * 进度条构建器
 */
class ProgressBarBuilder extends ComponentBuilder
{
    /**
     * 设置进度值
     *
     * @param int $value 进度值 (0-100)
     * @return $this
     */
    public function value(int $value): self
    {
        $this->config['value'] = max(0, min(100, $value));
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
            $this->config['value'] = $this->stateManager->get($stateKey);
        }
        
        return $this;
    }

    /**
     * 构建进度条组件
     *
     * @return CData 进度条句柄
     */
    protected function buildComponent(): CData
    {
        // 创建进度条
        $this->handle = ProgressBar::create();

        // 设置初始值
        if (isset($this->config['value'])) {
            ProgressBar::setValue($this->handle, $this->config['value']);
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
        return 'progressbar';
    }

    /**
     * 获取进度值
     *
     * @return int
     */
    public function getValue(): int
    {
        if ($this->handle) {
            return ProgressBar::value($this->handle);
        }
        return $this->config['value'] ?? 0;
    }

    /**
     * 设置进度值（动态更新）
     *
     * @param mixed $value 进度值 (0-100)
     * @return $this
     */
    public function setValue(mixed $value): self
    {
        $value = max(0, min(100, (int)$value));
        $this->config['value'] = $value;
        
        if ($this->handle) {
            ProgressBar::setValue($this->handle, $value);
        }
        
        // 更新绑定的状态
        if (isset($this->config['bind']) && $this->stateManager) {
            $this->stateManager->set($this->config['bind'], $value);
        }
        
        return $this;
    }

    
}
