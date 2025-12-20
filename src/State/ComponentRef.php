<?php

namespace Kingbes\Libui\View\State;

use FFI\CData;

/**
 * 组件引用 - 提供访问其他组件数据的能力
 * 
 * 通过ComponentInterface解耦对具体Builder实现的依赖
 */
class ComponentRef
{
    private ComponentInterface $component;
    private ?CData $handle = null;
    private string $id;

    public function __construct(string $id, ComponentInterface $component)
    {
        $this->id = $id;
        $this->component = $component;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getComponent(): ComponentInterface
    {
        return $this->component;
    }

    public function getHandle(): ?CData
    {
        return $this->handle ?? $this->component->getHandle();
    }

    public function setHandle(CData $handle): void
    {
        $this->handle = $handle;
    }

    /**
     * 获取组件当前值
     */
    public function getValue(): mixed
    {
        return $this->component->getValue();
    }

    /**
     * 设置组件值
     */
    public function setValue(mixed $value): void
    {
        $this->component->setValue($value);
    }

    /**
     * 获取组件配置
     */
    public function getConfig(string $key = null): mixed
    {
        if ($key) {
            return $this->component->getConfigValue($key);
        }
        return $this->component->getConfig();
    }

    /**
     * 调用组件方法
     */
    public function call(string $method, ...$args): mixed
    {
        return $this->component->call($method, ...$args);
    }

    /**
     * 获取组件类型
     */
    public function getType(): string
    {
        return $this->component->getType();
    }
}