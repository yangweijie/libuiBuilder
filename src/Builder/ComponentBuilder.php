<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\State\ComponentInterface;
use Kingbes\Libui\View\Core\Event\EventDispatcher;
use Kingbes\Libui\View\Core\Config\ConfigManager;

/**
 * 组件构建器抽象基类
 * 
 * 所有 UI 组件构建器的基类，提供链式调用和通用功能
 * 支持依赖注入和事件系统
 */
abstract class ComponentBuilder implements ComponentInterface
{
    /** @var array 组件配置 */
    protected array $config = [];

    /** @var array 事件处理器 */
    protected array $events = [];

    /** @var string 组件 ID */
    protected ?string $id = null;

    /** @var CData|null 构建后的控件句柄 */
    protected ?CData $handle = null;

    /** @var StateManager|null 状态管理器实例 */
    protected ?StateManager $stateManager = null;

    /** @var EventDispatcher|null 事件分发器 */
    protected ?EventDispatcher $eventDispatcher = null;

    /** @var ConfigManager|null 配置管理器 */
    protected ?ConfigManager $configManager = null;

    /**
     * 设置组件 ID
     *
     * @param string $id 组件标识符
     * @return $this
     */
    public function id(string $id): self
    {
        $this->id = $id;
        $this->config['id'] = $id;
        
        // 注册到状态管理器
        if ($this->stateManager) {
            $this->stateManager->registerComponent($id, $this);
        }
        
        return $this;
    }

    /**
     * 获取组件 ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * 获取组件配置
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 设置配置项
     *
     * @param string $key 配置键
     * @param mixed $value 配置值
     * @return $this
     */
    public function setConfig(string $key, mixed $value): self
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * 获取配置项
     *
     * @param string $key 配置键
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * 设置状态管理器
     *
     * @param StateManager $manager
     * @return $this
     */
    public function setStateManager(StateManager $manager): self
    {
        $this->stateManager = $manager;
        return $this;
    }

    /**
     * 获取状态管理器
     *
     * @return StateManager|null
     */
    public function getStateManager(): ?StateManager
    {
        return $this->stateManager;
    }

    /**
     * 设置事件分发器
     *
     * @param EventDispatcher $dispatcher
     * @return $this
     */
    public function setEventDispatcher(EventDispatcher $dispatcher): self
    {
        $this->eventDispatcher = $dispatcher;
        return $this;
    }

    /**
     * 获取事件分发器
     *
     * @return EventDispatcher|null
     */
    public function getEventDispatcher(): ?EventDispatcher
    {
        return $this->eventDispatcher;
    }

    /**
     * 设置配置管理器
     *
     * @param ConfigManager $manager
     * @return $this
     */
    public function setConfigManager(ConfigManager $manager): self
    {
        $this->configManager = $manager;
        return $this;
    }

    /**
     * 获取配置管理器
     *
     * @return ConfigManager|null
     */
    public function getConfigManager(): ?ConfigManager
    {
        return $this->configManager;
    }

    /**
     * 注册点击事件处理器
     *
     * @param callable $callback 回调函数
     * @return $this
     */
    public function onClick(callable $callback): self
    {
        $this->events['onClick'] = $callback;
        return $this;
    }

    /**
     * 注册值改变事件处理器
     *
     * @param callable $callback 回调函数
     * @return $this
     */
    public function onChange(callable $callback): self
    {
        $this->events['onChange'] = $callback;
        return $this;
    }

    /**
     * 注册自定义事件
     *
     * @param string $eventName 事件名
     * @param callable $callback 回调函数
     * @return $this
     */
    public function on(string $eventName, callable $callback): self
    {
        $this->events[$eventName] = $callback;
        return $this;
    }

    

    /**
     * 获取事件处理器
     *
     * @param string $eventName
     * @return callable|null
     */
    public function getEvent(string $eventName): ?callable
    {
        return $this->events[$eventName] ?? null;
    }

    /**
     * 获取所有事件
     *
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * 获取控件句柄
     *
     * @return CData|null
     */
    public function getHandle(): ?CData
    {
        return $this->handle;
    }

    /**
     * 设置控件句柄
     *
     * @param CData $handle
     * @return $this
     */
    public function setHandle(CData $handle): self
    {
        $this->handle = $handle;
        return $this;
    }

    /**
     * 构建组件
     * 
     * 使用模板方法模式，统一状态注册逻辑
     *
     * @return CData 控件句柄
     */
    public function build(): CData
    {
        // 调用子类的具体构建逻辑
        $this->handle = $this->buildComponent();
        
        // 调用子类的构建后处理（可选）
        $this->afterBuild();
        
        // 统一的状态注册逻辑
        if ($this->id && $this->stateManager) {
            $this->stateManager->registerComponent($this->id, $this);
        }
        
        return $this->handle;
    }

    /**
     * 构建具体组件
     * 
     * 子类必须实现此方法来创建实际的 UI 控件
     *
     * @return CData 控件句柄
     */
    abstract protected function buildComponent(): CData;

    /**
     * 构建后处理
     * 
     * 子类可以覆盖此方法以执行构建后的特殊处理
     * 如事件绑定等
     *
     * @return void
     */
    protected function afterBuild(): void
    {
        // 默认实现为空，子类可以覆盖
    }

    

    /**
     * 获取组件类型
     *
     * @return string
     */
    abstract public function getType(): string;

    /**
     * 获取组件值（默认实现）
     * 
     * 子类可以覆盖此方法以提供特殊的值获取逻辑
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->config['value'] ?? null;
    }

    /**
     * 设置组件值（默认实现）
     * 
     * 子类可以覆盖此方法以提供特殊的值设置逻辑
     *
     * @param mixed $value
     * @return self
     */
    public function setValue(mixed $value): self
    {
        $this->config['value'] = $value;
        
        // 如果组件已构建，尝试更新显示值
        if ($this->handle) {
            $this->updateComponentValue($value);
        }
        
        // 更新绑定的状态
        if (isset($this->config['bind']) && $this->stateManager) {
            $this->stateManager->set($this->config['bind'], $value);
        }
        
        return $this;
    }

    /**
     * 更新组件的显示值（默认实现）
     * 
     * 子类可以覆盖此方法以实现特定的值更新逻辑
     *
     * @param mixed $value
     * @return void
     */
    protected function updateComponentValue(mixed $value): void
    {
        // 默认实现为空，子类可以覆盖
    }

    /**
     * 调用组件方法（统一实现）
     *
     * @param string $method 方法名
     * @param mixed ...$args 参数
     * @return mixed
     */
    public function call(string $method, ...$args): mixed
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$args);
        }
        
        throw new \BadMethodCallException("Method {$method} does not exist on " . static::class);
    }

    /**
     * 标准状态绑定方法
     * 
     * 为支持状态绑定的组件提供统一的绑定逻辑
     *
     * @param string $stateKey 状态键名
     * @return self
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

    
}
