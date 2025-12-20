<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Entry;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Core\Event\ValueChangeEvent;

/**
 * 输入框构建器
 */
class EntryBuilder extends ComponentBuilder
{
    /**
     * 设置占位符文本
     *
     * @param string $placeholder 占位符
     * @return $this
     */
    public function placeholder(string $placeholder): self
    {
        $this->config['placeholder'] = $placeholder;
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
     * 设置为密码输入框
     *
     * @return $this
     */
    public function password(): self
    {
        $this->config['type'] = 'password';
        return $this;
    }

    /**
     * 设置为搜索输入框
     *
     * @return $this
     */
    public function search(): self
    {
        $this->config['type'] = 'search';
        return $this;
    }

    /**
     * 设置为只读
     *
     * @param bool $readOnly 是否只读
     * @return $this
     */
    public function readOnly(bool $readOnly = true): self
    {
        $this->config['readOnly'] = $readOnly;
        return $this;
    }

    /**
     * 构建输入框组件
     *
     * @return CData 输入框句柄
     */
    protected function buildComponent(): CData
    {
        $type = $this->config['type'] ?? 'normal';
        $isMultiline = $this->config['multiline'] ?? false;
        $isNonWrapping = $this->config['nonWrapping'] ?? false;
        
        // 根据类型创建输入框
        if ($isMultiline) {
            if ($isNonWrapping) {
                $this->handle = Entry::createNonWrappingMultiline();
            } else {
                $this->handle = Entry::createMultiline();
            }
        } else {
            switch ($type) {
                case 'password':
                    $this->handle = Entry::createPwd();
                    break;
                case 'search':
                    $this->handle = Entry::createSearch();
                    break;
                default:
                    $this->handle = Entry::create();
                    break;
            }
        }

        // 设置初始值
        if (isset($this->config['value'])) {
            Entry::setText($this->handle, $this->config['value']);
        }

        // 设置只读
        if (isset($this->config['readOnly'])) {
            Entry::setReadOnly($this->handle, $this->config['readOnly']);
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
        // 绑定值改变事件
        if (isset($this->events['onChange']) || isset($this->config['bind'])) {
            $callback = $this->events['onChange'] ?? null;
            $stateKey = $this->config['bind'] ?? null;
            $stateManager = $this->stateManager;
            $eventDispatcher = $this->eventDispatcher;
            $oldValue = $this->config['value'] ?? '';
            
            Entry::onChanged($this->handle, function($entry) use ($callback, $stateKey, $stateManager, $eventDispatcher, &$oldValue) {
                $value = Entry::text($entry);
                
                // 通过事件分发器触发（如果可用）
                if ($eventDispatcher) {
                    $event = new ValueChangeEvent($this, $oldValue, $value, $stateManager);
                    $eventDispatcher->dispatch($event);
                }
                
                // 更新状态
                if ($stateKey && $stateManager) {
                    echo "[ENTRY_DEBUG] StateManager实例ID: " . spl_object_hash($stateManager) . "\n";
                    echo "[ENTRY_DEBUG] 更新状态: {$stateKey} = '{$value}'\n";
                    $stateManager->set($stateKey, $value);
                    echo "[ENTRY_DEBUG] 状态更新完成\n";
                }
                
                // 调用传统回调
                if ($callback) {
                    if ($stateManager) {
                        $callback($value, $this, $stateManager);
                    } else {
                        $callback($value, $this);
                    }
                }
                
                $oldValue = $value;
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
        return 'entry';
    }

    /**
     * 获取输入框值
     *
     * @return string|null
     */
    public function getValue(): ?string
    {
        if ($this->handle) {
            return Entry::text($this->handle);
        }
        return $this->config['value'] ?? null;
    }

    /**
     * 设置输入框值（动态更新）
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue(mixed $value): self
    {
        $this->config['value'] = $value;
        
        if ($this->handle) {
            Entry::setText($this->handle, (string)$value);
        }
        
        // 更新绑定的状态
        if (isset($this->config['bind']) && $this->stateManager) {
            $this->stateManager->set($this->config['bind'], $value);
        }
        
        return $this;
    }

    /**
     * 设置为多行输入框
     *
     * @return $this
     */
    public function multiline(): self
    {
        $this->config['multiline'] = true;
        return $this;
    }

    /**
     * 设置为不换行多行输入框
     *
     * @return $this
     */
    public function nonWrapping(): self
    {
        $this->config['multiline'] = true;
        $this->config['nonWrapping'] = true;
        return $this;
    }

    
}
