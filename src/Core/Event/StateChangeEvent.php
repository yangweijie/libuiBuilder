<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Core\Event;

use League\Event\HasEventName;

/**
 * 状态改变事件
 */
class StateChangeEvent implements HasEventName
{
    private string $key;
    private mixed $oldValue;
    private mixed $newValue;

    public function __construct(string $key, mixed $oldValue, mixed $newValue)
    {
        $this->key = $key;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    /**
     * 实现 HasEventName 接口
     */
    public function eventName(): string
    {
        return static::class;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getOldValue(): mixed
    {
        return $this->oldValue;
    }

    public function getNewValue(): mixed
    {
        return $this->newValue;
    }

    public function getTimestamp(): string
    {
        return date('Y-m-d H:i:s');
    }
}