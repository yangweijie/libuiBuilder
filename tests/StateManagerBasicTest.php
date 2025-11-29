<?php

use Kingbes\Libui\View\State\StateManager;

beforeEach(function () {
    // 重置状态管理器实例
    $reflection = new ReflectionClass(StateManager::class);
    $instance = $reflection->getProperty('instance');
    $instance->setAccessible(true);
    $instance->setValue(null, null);
});

test('StateManager 单例模式', function () {
    $manager1 = StateManager::instance();
    $manager2 = StateManager::instance();
    
    expect($manager1)->toBeInstanceOf(StateManager::class);
    expect($manager1)->toBe($manager2);
});

test('StateManager 基础操作', function () {
    $manager = StateManager::instance();
    
    // 测试设置和获取
    $manager->set('test', 'value');
    expect($manager->get('test'))->toBe('value');
    
    // 测试默认值
    expect($manager->get('nonexistent'))->toBeNull();
    expect($manager->get('nonexistent', 'default'))->toBe('default');
    
    // 测试批量更新
    $manager->update([
        'key1' => 'value1',
        'key2' => 'value2'
    ]);
    
    expect($manager->get('key1'))->toBe('value1');
    expect($manager->get('key2'))->toBe('value2');
});

test('StateManager 监听器', function () {
    $manager = StateManager::instance();
    
    $called = false;
    $newValue = null;
    $oldValue = null;
    
    $manager->watch('test', function($new, $old) use (&$called, &$newValue, &$oldValue) {
        $called = true;
        $newValue = $new;
        $oldValue = $old;
    });
    
    $manager->set('test', 'new_value');
    
    expect($called)->toBeTrue();
    expect($newValue)->toBe('new_value');
    expect($oldValue)->toBeNull();
});