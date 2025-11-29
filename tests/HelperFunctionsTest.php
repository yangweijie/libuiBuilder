<?php

use Kingbes\Libui\View\State\StateManager;

beforeEach(function () {
    // 重置状态管理器
    $reflection = new ReflectionClass(StateManager::class);
    $instance = $reflection->getProperty('instance');
    $instance->setAccessible(true);
    $instance->setValue(null, null);
});

describe('Helper 函数测试', function () {
    
    test('helper 函数存在性检查', function () {
        // 检查 helper 函数是否已定义
        expect(function_exists('state'))->toBeTrue();
        expect(function_exists('watch'))->toBeTrue();
    });
    
    test('state() 函数无参数调用', function () {
        $manager = state();
        
        expect($manager)->toBeObject();
        expect(get_class($manager))->toContain('StateManager');
    });
    
    test('state() 函数设置单个值', function () {
        state('test_key', 'test_value');
        
        $manager = state();
        $value = $manager->get('test_key');
        
        expect($value)->toBe('test_value');
    });
    
    test('state() 函数获取值', function () {
        state('existing_key', 'existing_value');
        
        $value = state('existing_key');
        
        expect($value)->toBe('existing_value');
    });
    
    test('state() 函数批量设置', function () {
        state([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3'
        ]);
        
        $value1 = state('key1');
        $value2 = state('key2');
        $value3 = state('key3');
        
        expect($value1)->toBe('value1');
        expect($value2)->toBe('value2');
        expect($value3)->toBe('value3');
    });
    
    test('state() 函数获取不存在的键', function () {
        $value = state('nonexistent_key');
        
        expect($value)->toBeNull();
    });
    
    test('state() 函数覆盖值', function () {
        state('override_key', 'original_value');
        state('override_key', 'new_value');
        
        $value = state('override_key');
        
        expect($value)->toBe('new_value');
    });
    
    test('state() 函数处理复杂值', function () {
        $complexValue = [
            'nested' => [
                'key' => 'nested_value'
            ],
            'array' => [1, 2, 3],
            'boolean' => true
        ];
        
        state('complex_key', $complexValue);
        
        $retrievedValue = state('complex_key');
        
        expect($retrievedValue)->toEqual($complexValue);
        expect($retrievedValue['nested']['key'])->toBe('nested_value');
        expect($retrievedValue['array'])->toEqual([1, 2, 3]);
        expect($retrievedValue['boolean'])->toBeTrue();
    });
    
    test('watch() 函数基本功能', function () {
        $callbackCalled = false;
        $newValue = null;
        $oldValue = null;
        
        watch('watch_key', function($new, $old) use (&$callbackCalled, &$newValue, &$oldValue) {
            $callbackCalled = true;
            $newValue = $new;
            $oldValue = $old;
        });
        
        state('watch_key', 'trigger_value');
        
        expect($callbackCalled)->toBeTrue();
        expect($newValue)->toBe('trigger_value');
        expect($oldValue)->toBeNull();
    });
    
    test('watch() 函数多次触发', function () {
        $callCount = 0;
        $values = [];
        
        watch('counter', function($new, $old) use (&$callCount, &$values) {
            $callCount++;
            $values[] = ['old' => $old, 'new' => $new];
        });
        
        state('counter', 1);
        state('counter', 2);
        state('counter', 3);
        
        expect($callCount)->toBe(3);
        expect($values[0])->toEqual(['old' => null, 'new' => 1]);
        expect($values[1])->toEqual(['old' => 1, 'new' => 2]);
        expect($values[2])->toEqual(['old' => 2, 'new' => 3]);
    });
    
    test('watch() 函数多个监听器', function () {
        $listener1Called = false;
        $listener2Called = false;
        
        watch('shared_key', function() use (&$listener1Called) {
            $listener1Called = true;
        });
        
        watch('shared_key', function() use (&$listener2Called) {
            $listener2Called = true;
        });
        
        state('shared_key', 'shared_value');
        
        expect($listener1Called)->toBeTrue();
        expect($listener2Called)->toBeTrue();
    });
    
    test('state() 和 watch() 函数集成', function () {
        $updates = [];
        
        watch('integrated_key', function($new, $old) use (&$updates) {
            $updates[] = "Changed from {$old} to {$new}";
        });
        
        state('integrated_key', 'first');
        state('integrated_key', 'second');
        state('integrated_key', 'third');
        
        expect(count($updates))->toBe(3);
        expect($updates[0])->toBe('Changed from  to first');
        expect($updates[1])->toBe('Changed from first to second');
        expect($updates[2])->toBe('Changed from second to third');
    });
    
    test('state() 函数处理特殊字符', function () {
        $specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        state('special_key', $specialChars);
        
        $retrievedValue = state('special_key');
        
        expect($retrievedValue)->toBe($specialChars);
    });
    
    test('state() 函数处理中文', function () {
        $chineseText = '这是中文测试内容';
        
        state('chinese_key', $chineseText);
        
        $retrievedValue = state('chinese_key');
        
        expect($retrievedValue)->toBe($chineseText);
    });
    
    test('state() 函数处理空值', function () {
        state('empty_string', '');
        state('null_value', null);
        state('false_value', false);
        state('zero_value', 0);
        
        expect(state('empty_string'))->toBe('');
        expect(state('null_value'))->toBeNull();
        expect(state('false_value'))->toBeFalse();
        expect(state('zero_value'))->toBe(0);
    });
});