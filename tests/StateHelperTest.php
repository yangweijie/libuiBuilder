<?php

use Kingbes\Libui\View\State\StateManager;

beforeEach(function () {
    // 重置状态管理器
    $reflection = new ReflectionClass(StateManager::class);
    $instance = $reflection->getProperty('instance');
    $instance->setAccessible(true);
    $instance->setValue(null, null);
});

describe('state() 辅助函数', function () {
    
    it('无参数时返回 StateManager 实例', function () {
        $manager = state();
        
        expect($manager)->toBeInstanceOf(StateManager::class);
    });

    it('可以设置单个状态', function () {
        state('username', 'John');
        
        $value = state('username');
        
        expect($value)->toBe('John');
    });

    it('可以获取单个状态', function () {
        state('age', 25);
        
        $value = state('age');
        
        expect($value)->toBe(25);
    });

    it('可以批量设置状态', function () {
        state([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'role' => 'admin'
        ]);
        
        expect(state('name'))->toBe('Alice');
        expect(state('email'))->toBe('alice@example.com');
        expect(state('role'))->toBe('admin');
    });

    it('获取不存在的状态返回 null', function () {
        $value = state('nonexistent');
        
        expect($value)->toBeNull();
    });

    it('可以设置嵌套数组', function () {
        state('user', [
            'profile' => [
                'name' => 'Bob',
                'age' => 30
            ]
        ]);
        
        $user = state('user');
        
        expect($user['profile']['name'])->toBe('Bob');
        expect($user['profile']['age'])->toBe(30);
    });

    it('可以更新已存在的状态', function () {
        state('counter', 0);
        state('counter', 1);
        state('counter', 2);
        
        expect(state('counter'))->toBe(2);
    });
});

describe('watch() 辅助函数', function () {
    
    it('可以监听状态变化', function () {
        $oldValue = null;
        $newValue = null;

        watch('username', function($new, $old) use (&$newValue, &$oldValue) {
            $newValue = $new;
            $oldValue = $old;
        });

        state('username', 'Alice');

        expect($newValue)->toBe('Alice');
        expect($oldValue)->toBeNull();
    });

    it('可以监听多次变化', function () {
        $changes = [];

        watch('count', function($new, $old) use (&$changes) {
            $changes[] = ['old' => $old, 'new' => $new];
        });

        state('count', 1);
        state('count', 2);
        state('count', 3);

        expect(count($changes))->toBe(3);
        expect($changes[0])->toBe(['old' => null, 'new' => 1]);
        expect($changes[1])->toBe(['old' => 1, 'new' => 2]);
        expect($changes[2])->toBe(['old' => 2, 'new' => 3]);
    });

    it('可以注册多个监听器', function () {
        $listener1Called = false;
        $listener2Called = false;

        watch('value', function() use (&$listener1Called) {
            $listener1Called = true;
        });

        watch('value', function() use (&$listener2Called) {
            $listener2Called = true;
        });

        state('value', 'test');

        expect($listener1Called)->toBeTrue();
        expect($listener2Called)->toBeTrue();
    });
});

describe('状态管理集成测试', function () {
    
    it('可以实现简单的响应式计数器', function () {
        $displayValue = '';

        watch('count', function($new) use (&$displayValue) {
            $displayValue = "当前计数: $new";
        });

        state('count', 0);
        expect($displayValue)->toBe('当前计数: 0');

        state('count', 5);
        expect($displayValue)->toBe('当前计数: 5');

        state('count', 10);
        expect($displayValue)->toBe('当前计数: 10');
    });

    it('可以实现表单验证', function () {
        $errors = [];

        watch('email', function($new) use (&$errors) {
            if (!filter_var($new, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = '邮箱格式不正确';
            } else {
                unset($errors['email']);
            }
        });

        state('email', 'invalid');
        expect($errors)->toHaveKey('email');

        state('email', 'valid@example.com');
        expect($errors)->not->toHaveKey('email');
    });

    it('可以实现状态同步', function () {
        $synced = [];

        watch('source', function($new) use (&$synced) {
            $synced['target'] = $new;
        });

        state('source', 'value1');
        expect($synced['target'])->toBe('value1');

        state('source', 'value2');
        expect($synced['target'])->toBe('value2');
    });
});
