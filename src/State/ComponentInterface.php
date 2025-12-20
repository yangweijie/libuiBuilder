<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\State;

use FFI\CData;

/**
 * 组件接口 - 解耦ComponentRef与具体Builder实现
 */
interface ComponentInterface
{
    /**
     * 获取组件ID
     */
    public function getId(): ?string;

    /**
     * 获取组件当前值
     */
    public function getValue(): mixed;

    /**
     * 设置组件值
     */
    public function setValue(mixed $value): self;

    /**
     * 获取组件配置
     */
    public function getConfig(): array;

    /**
     * 获取指定配置项
     */
    public function getConfigValue(string $key, mixed $default = null): mixed;

    /**
     * 获取组件类型
     */
    public function getType(): string;

    /**
     * 获取控件句柄
     */
    public function getHandle(): ?CData;

    /**
     * 调用组件方法
     */
    public function call(string $method, ...$args): mixed;
}