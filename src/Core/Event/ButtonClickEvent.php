<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Core\Event;

use Kingbes\Libui\View\Builder\ComponentBuilder;
use Kingbes\Libui\View\State\StateManager;
use League\Event\HasEventName;

/**
 * 按钮点击事件
 */
class ButtonClickEvent implements HasEventName
{
    private ComponentBuilder $component;
    private ?StateManager $stateManager;

    public function __construct(ComponentBuilder $component, ?StateManager $stateManager = null)
    {
        $this->component = $component;
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