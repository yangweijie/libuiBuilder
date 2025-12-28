<?php

namespace Kingbes\Libui\View\State;

use FFI\CData;
use Kingbes\Libui\View\Validation\ComponentBuilder;

/**
 * 组件引用 - 提供访问其他组件数据的能力
 */
class ComponentRef
{
    private ComponentBuilder $component;
    private ?CData $handle = null;
    private string $id;

    public function __construct(string $id, ComponentBuilder $component)
    {
        $this->id = $id;
        $this->component = $component;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getComponent(): ComponentBuilder
    {
        return $this->component;
    }

    public function getHandle(): ?CData
    {
        return $this->handle;
    }

    public function setHandle(CData $handle): void
    {
        $this->handle = $handle;
    }

    /**
     * 获取组件当前值
     */
    public function getValue()
    {
        return $this->component->getValue();
    }

    /**
     * 设置组件值
     */
    public function setValue($value): void
    {
        $this->component->setValue($value);
    }

    /**
     * 获取组件配置
     */
    public function getConfig(string $key = null)
    {
        return $key ? $this->component->getConfig($key) : $this->component->getConfig();
    }

    /**
     * 调用组件方法
     */
    public function call(string $method, ...$args)
    {
        return $this->component->$method(...$args);
    }
}