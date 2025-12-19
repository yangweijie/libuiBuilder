<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Core\Event;

use Kingbes\Libui\View\Builder\ComponentBuilder;
use Kingbes\Libui\View\State\StateManager;
use League\Event\HasEventName;

/**
 * 值改变事件
 */
class ValueChangeEvent implements HasEventName
{
    private ComponentBuilder $component;
    private mixed $oldValue;
    private mixed $newValue;
    private ?StateManager $stateManager;

    public function __construct(ComponentBuilder $component, mixed $oldValue, mixed $newValue, ?StateManager $stateManager = null)
    {
        $this->component = $component;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
        $this->stateManager = $stateManager;
    }

    /**
     * 实现 HasEventName 接口
     */
    public function eventName(): string
    {
        return static::class;
    }

    public function getComponent(): ComponentBuilder
    {
        return $this->component;
    }

    public function getOldValue(): mixed
    {
        return $this->oldValue;
    }

    public function getNewValue(): mixed
    {
        return $this->newValue;
    }

    public function getStateManager(): ?StateManager
    {
        return $this->stateManager;
    }

    public function getComponentId(): ?string
    {
        return $this->component->getId();
    }

    public function getTimestamp(): string
    {
        return date('Y-m-d H:i:s');
    }
}