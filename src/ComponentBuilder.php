<?php

namespace Kingbes\Libui\View;

use BadMethodCallException;
use InvalidArgumentException;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\State\ComponentRef;
use FFI\CData;

abstract class ComponentBuilder
{
    protected array $config;
    protected ?CData $handle = null;
    protected array $children = [];
    protected ?ComponentBuilder $parent = null;
    protected ?string $id = null;
    protected ?ComponentRef $ref = null;

    // 数据绑定
    protected ?string $boundState = null;
    protected array $eventHandlers = [];


    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * 获取默认配置
     */
    abstract protected function getDefaultConfig(): array;

    /**
     * 创建原生控件实例
     */
    abstract protected function createNativeControl(): CData;

    /**
     * 应用配置到原生控件
     */
    abstract protected function applyConfig(): void;

    /**
     * 添加子组件（容器控件重写此方法）
     */
    public function contains(array $children): static
    {
        foreach ($children as $child) {
            $this->addChild($child);
        }
        return $this;
    }

    /**
     * 添加单个子组件
     */
    public function addChild(ComponentBuilder $child): static
    {
        if (!$this->canHaveChildren()) {
            throw new InvalidArgumentException(static::class . ' cannot have children');
        }

        $this->children[] = $child;
        $child->parent = $this;
        return $this;
    }

    /**
     * 是否可以包含子组件
     */
    protected function canHaveChildren(): bool
    {
        return false; // 大部分控件不能包含子组件
    }

    /**
     * 构建子组件
     */
    protected function buildChildren(): void
    {
        // 容器控件重写此方法
    }

    /**
     * 显示组件（只对窗口有效）
     */
    public function show(): void
    {
        throw new BadMethodCallException('Only Window can be shown directly');
    }

    /**
     * 获取配置值
     */
    protected function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * 设置配置
     */
    public function setConfig(string $key, $value): static
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * 链式配置方法
     */
    public function __call(string $name, array $args): static
    {
        if (count($args) === 1) {
            $this->setConfig($name, $args[0]);
        }
        return $this;
    }

    /**
     * 设置组件ID，用于引用
     */
    public function id(string $id): static
    {
        $this->id = $id;
        $this->ref = new ComponentRef($id, $this);
        StateManager::instance()->registerComponent($id, $this->ref);
        return $this;
    }

    /**
     * 绑定到状态管理器
     */
    public function bind(string $stateKey): static
    {
        $this->boundState = $stateKey;

        // 监听状态变化并自动更新组件
        StateManager::instance()->watch($stateKey, function($newValue) {
            $this->setValue($newValue);
        });

        return $this;
    }

    /**
     * 添加事件处理器
     */
    public function on(string $event, callable $handler): static
    {
        if (!isset($this->eventHandlers[$event])) {
            $this->eventHandlers[$event] = [];
        }
        $this->eventHandlers[$event][] = $handler;
        return $this;
    }

    /**
     * 触发事件
     */
    protected function emit(string $event, ...$args): void
    {
        if (isset($this->eventHandlers[$event])) {
            foreach ($this->eventHandlers[$event] as $handler) {
                $handler($this, ...$args);
            }
        }

        // 如果绑定了状态，自动更新状态
        if ($this->boundState && $event === 'change') {
            StateManager::instance()->set($this->boundState, $this->getValue());
        }
    }

    /**
     * 获取组件当前值 - 子类实现
     */
    public function getValue()
    {
        return $this->getConfig('value');
    }

    /**
     * 设置组件值 - 子类实现
     */
    public function setValue($value): void
    {
        $this->setConfig('value', $value);
    }

    /**
     * 获取其他组件的引用
     */
    protected function getRef(string $id): ?ComponentRef
    {
        return StateManager::instance()->getComponent($id);
    }

    /**
     * 获取状态管理器
     */
    protected function state(): StateManager
    {
        return StateManager::instance();
    }

    public function build(): CData
    {
        if ($this->handle === null) {
            $this->handle = $this->createNativeControl();

            // 设置引用的原生句柄
            if ($this->ref) {
                $this->ref->setHandle($this->handle);
            }

            $this->applyConfig();
            $this->buildChildren();

            // 如果绑定了状态，设置初始值
            if ($this->boundState) {
                $initialValue = StateManager::instance()->get($this->boundState);
                if ($initialValue !== null) {
                    $this->setValue($initialValue);
                }
            }
        }
        return $this->handle;
    }

    // ... 其他现有方法保持不变
}