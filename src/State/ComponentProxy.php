<?php

namespace Kingbes\Libui\View\State;

/**
 * 组件代理类 - 支持链式调用语法糖
 */
class ComponentProxy
{
    private ComponentBuilder $component;

    public function __construct(ComponentBuilder $component)
    {
        $this->component = $component;
    }

    public function getValue()
    {
        return $this->component->getValue();
    }

    public function setValue($value): self
    {
        $this->component->setValue($value);
        return $this;
    }

    public function getText(): string
    {
        return $this->component->getConfig('text', '');
    }

    public function setText(string $text): self
    {
        $this->component->setConfig('text', $text);
        return $this;
    }

    public function getConfig(string $key = null)
    {
        return $key ? $this->component->getConfig($key) : $this->component->getConfig();
    }

    public function setConfig(string $key, $value): self
    {
        $this->component->setConfig($key, $value);
        return $this;
    }
}