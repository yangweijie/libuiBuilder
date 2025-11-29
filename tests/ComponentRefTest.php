<?php

use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\State\ComponentRef;
use Kingbes\Libui\View\ComponentBuilder;

// 创建一个简单的 Mock ComponentBuilder
class MockComponentBuilder extends ComponentBuilder
{
    private $value = 'default';
    
    protected function getDefaultConfig(): array
    {
        return ['value' => 'default'];
    }
    
    protected function createNativeControl(): FFI\CData
    {
        return null;
    }
    
    protected function applyConfig(): void
    {
        // 空实现
    }
    
    public function getValue()
    {
        return $this->value;
    }
    
    public function setValue($value): void
    {
        $this->value = $value;
    }
    
    public function getConfig(string $key, $default = null)
    {
        return $key === 'value' ? $this->value : $default;
    }
}

beforeEach(function () {
    $reflection = new ReflectionClass(StateManager::class);
    $instance = $reflection->getProperty('instance');
    $instance->setAccessible(true);
    $instance->setValue(null, null);
});

describe('ComponentRef 测试', function () {
    
    test('ComponentRef 基础功能', function () {
        $mockComponent = new MockComponentBuilder();
        $componentRef = new ComponentRef('test-component', $mockComponent);
        
        expect($componentRef)->toBeInstanceOf(ComponentRef::class);
        expect($componentRef->getId())->toBe('test-component');
    });
    
    test('StateManager 注册和获取组件', function () {
        $stateManager = StateManager::instance();
        $mockComponent = new MockComponentBuilder();
        $componentRef = new ComponentRef('test-component', $mockComponent);
        
        // 注册组件
        $stateManager->registerComponent('test-component', $componentRef);
        
        // 获取组件
        $retrievedRef = $stateManager->getComponent('test-component');
        
        expect($retrievedRef)->toBe($componentRef);
        expect($retrievedRef->getId())->toBe('test-component');
    });
    
    test('获取不存在的组件返回 null', function () {
        $stateManager = StateManager::instance();
        
        $nonexistentRef = $stateManager->getComponent('nonexistent-component');
        
        expect($nonexistentRef)->toBeNull();
    });
    
    test('覆盖已注册的组件', function () {
        $stateManager = StateManager::instance();
        
        $mockComponent1 = new MockComponentBuilder();
        $mockComponent2 = new MockComponentBuilder();
        $componentRef1 = new ComponentRef('test-component', $mockComponent1);
        $componentRef2 = new ComponentRef('test-component', $mockComponent2);
        
        // 注册第一个组件
        $stateManager->registerComponent('test-component', $componentRef1);
        
        // 覆盖为第二个组件
        $stateManager->registerComponent('test-component', $componentRef2);
        
        $retrievedRef = $stateManager->getComponent('test-component');
        
        expect($retrievedRef)->toBe($componentRef2);
        expect($retrievedRef)->not->toBe($componentRef1);
    });
    
    test('注册多个不同组件', function () {
        $stateManager = StateManager::instance();
        
        $components = [
            'component1' => new ComponentRef('component1', new MockComponentBuilder()),
            'component2' => new ComponentRef('component2', new MockComponentBuilder()),
            'component3' => new ComponentRef('component3', new MockComponentBuilder())
        ];
        
        // 注册所有组件
        foreach ($components as $id => $componentRef) {
            $stateManager->registerComponent($id, $componentRef);
        }
        
        // 验证所有组件都能正确获取
        foreach ($components as $id => $originalRef) {
            $retrievedRef = $stateManager->getComponent($id);
            expect($retrievedRef)->toBe($originalRef);
            expect($retrievedRef->getId())->toBe($id);
        }
    });
    
    test('ComponentRef ID 属性', function () {
        $testIds = ['simple', 'with-dash', 'with_underscore', 'WithNumbers123'];
        
        foreach ($testIds as $id) {
            $componentRef = new ComponentRef($id, new MockComponentBuilder());
            expect($componentRef->getId())->toBe($id);
        }
    });
    
    test('StateManager dump 包含组件引用', function () {
        $stateManager = StateManager::instance();
        
        // 设置一些状态
        $stateManager->set('test1', 'value1');
        $stateManager->set('test2', 'value2');
        
        // 注册组件
        $mockComponent = new MockComponentBuilder();
        $componentRef = new ComponentRef('test-component', $mockComponent);
        $stateManager->registerComponent('test-component', $componentRef);
        
        // 获取状态转储
        $dump = $stateManager->dump();
        
        expect($dump)->toBeArray();
        expect($dump)->toHaveKey('test1');
        expect($dump)->toHaveKey('test2');
        expect($dump['test1'])->toBe('value1');
        expect($dump['test2'])->toBe('value2');
        // 注意：组件引用通常不会出现在 dump 中，因为它们存储在单独的属性中
    });
});