<?php

use Kingbes\Libui\Declarative\Components\Component;

// 创建一个测试用的组件类
class TestComponent extends Component
{
    public function render(): FFI\CData
    {
        return null; // 简单返回 null 用于测试
    }

    public function getTagName(): string
    {
        return 'test';
    }

    public function getValue()
    {
        return null;
    }

    public function setValue($value): void
    {
        // 空实现
    }
}

it('can create a component with attributes', function () {
    $component = new TestComponent(['text' => 'Hello World']);
    expect($component->getAttribute('text'))->toBe('Hello World');
});

it('returns default value for non-existent attribute', function () {
    $component = new TestComponent();
    expect($component->getAttribute('nonexistent', 'default'))->toBe('default');
});

it('can set and get attributes', function () {
    $component = new TestComponent();
    $component->setAttribute('test', 'value');
    expect($component->getAttribute('test'))->toBe('value');
});

it('can set multiple attributes at once', function () {
    $component = new TestComponent();
    $component->setAttributes(['attr1' => 'value1', 'attr2' => 'value2']);
    expect($component->getAttribute('attr1'))->toBe('value1');
    expect($component->getAttribute('attr2'))->toBe('value2');
});

it('has a reference if provided in attributes', function () {
    $component = new TestComponent(['ref' => 'testRef']);
    expect($component->getRef())->toBe('testRef');
});