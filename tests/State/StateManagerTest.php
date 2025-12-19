<?php

declare(strict_types=1);

namespace Tests\State;

use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Builder\ButtonBuilder;
use Kingbes\Libui\View\Builder\EntryBuilder;
use Kingbes\Libui\View\Core\Event\EventDispatcher;
use Kingbes\Libui\View\Core\Event\StateChangeEvent;

uses(\Tests\TestCase::class);

describe('StateManager', function () {
    beforeEach(function () {
        StateManager::reset();
    });

    test('can get singleton instance', function () {
        $instance1 = StateManager::instance();
        $instance2 = StateManager::instance();
        
        expect($instance1)->toBe($instance2);
        expect($instance1)->toBeInstanceOf(StateManager::class);
    });

    test('resets correctly', function () {
        $state = StateManager::instance();
        $state->set('test', 'value');
        
        StateManager::reset();
        
        $newInstance = StateManager::instance();
        expect($newInstance->has('test'))->toBeFalse();
    });

    test('sets and gets values', function () {
        $state = StateManager::instance();
        
        $state->set('name', 'John');
        expect($state->get('name'))->toBe('John');
        
        $state->set('age', 30);
        expect($state->get('age'))->toBe(30);
    });

    test('gets value with default', function () {
        $state = StateManager::instance();
        
        $result = $state->get('nonexistent', 'default');
        expect($result)->toBe('default');
    });

    test('gets null for non-existent key without default', function () {
        $state = StateManager::instance();
        
        $result = $state->get('nonexistent');
        expect($result)->toBeNull();
    });

    test('checks if key exists', function () {
        $state = StateManager::instance();
        
        $state->set('exists', 'value');
        
        expect($state->has('exists'))->toBeTrue();
        expect($state->has('nonexistent'))->toBeFalse();
    });

    test('deletes keys', function () {
        $state = StateManager::instance();
        
        $state->set('key', 'value');
        expect($state->has('key'))->toBeTrue();
        
        $state->delete('key');
        expect($state->has('key'))->toBeFalse();
    });

    test('gets all state', function () {
        $state = StateManager::instance();
        
        $state->set('a', 1);
        $state->set('b', 2);
        $state->set('c', 3);
        
        $all = $state->getAll();
        
        expect($all)->toBeArray();
        expect($all['a'])->toBe(1);
        expect($all['b'])->toBe(2);
        expect($all['c'])->toBe(3);
    });

    test('updates multiple values at once', function () {
        $state = StateManager::instance();
        
        $state->update([
            'name' => 'John',
            'age' => 30,
            'active' => true,
        ]);
        
        expect($state->get('name'))->toBe('John');
        expect($state->get('age'))->toBe(30);
        expect($state->get('active'))->toBeTrue();
    });

    test('clears all state', function () {
        $state = StateManager::instance();
        
        $state->set('a', 1);
        $state->set('b', 2);
        
        $state->clear();
        
        expect($state->getAll())->toBeEmpty();
    });

    test('watches state changes', function () {
        $state = StateManager::instance();
        
        $capturedNew = null;
        $capturedOld = null;
        
        $state->watch('counter', function ($new, $old) use (&$capturedNew, &$capturedOld) {
            $capturedNew = $new;
            $capturedOld = $old;
        });
        
        $state->set('counter', 1);
        expect($capturedNew)->toBe(1);
        expect($capturedOld)->toBeNull();
        
        $state->set('counter', 2);
        expect($capturedNew)->toBe(2);
        expect($capturedOld)->toBe(1);
    });

    test('triggers multiple listeners for same key', function () {
        $state = StateManager::instance();
        
        $count1 = 0;
        $count2 = 0;
        
        $state->watch('test', function () use (&$count1) {
            $count1++;
        });
        
        $state->watch('test', function () use (&$count2) {
            $count2++;
        });
        
        $state->set('test', 'value');
        
        expect($count1)->toBe(1);
        expect($count2)->toBe(1);
    });

    test('only triggers listeners when value actually changes', function () {
        $state = StateManager::instance();
        
        $count = 0;
        $state->watch('test', function () use (&$count) {
            $count++;
        });
        
        $state->set('test', 'value');
        $state->set('test', 'value'); // Same value
        $state->set('test', 'new');   // Different value
        
        expect($count)->toBe(2);
    });

    test('triggers listeners for nested keys', function () {
        $state = StateManager::instance();
        
        $captured = [];
        
        $state->watch('user.name', function ($new, $old) use (&$captured) {
            $captured[] = ['new' => $new, 'old' => $old];
        });
        
        $state->set('user.name', 'John');
        $state->set('user.name', 'Jane');
        
        expect(count($captured))->toBe(2);
        expect($captured[0]['new'])->toBe('John');
        expect($captured[1]['new'])->toBe('Jane');
    });

    test('registers component', function () {
        $state = StateManager::instance();
        
        $button = new ButtonBuilder();
        $button->id('testBtn');
        
        $state->registerComponent('testBtn', $button);
        
        $retrieved = $state->getComponent('testBtn');
        expect($retrieved)->toBe($button);
    });

    test('gets registered component', function () {
        $state = StateManager::instance();
        
        $entry = new EntryBuilder();
        $entry->id('username');
        
        $state->registerComponent('username', $entry);
        
        $retrieved = $state->getComponent('username');
        expect($retrieved)->toBe($entry);
        expect($retrieved->getId())->toBe('username');
    });

    test('returns null for non-existent component', function () {
        $state = StateManager::instance();
        
        $result = $state->getComponent('nonexistent');
        expect($result)->toBeNull();
    });

    test('unregisters component', function () {
        $state = StateManager::instance();
        
        $button = new ButtonBuilder();
        $state->registerComponent('btn', $button);
        
        expect($state->getComponent('btn'))->not->toBeNull();
        
        $state->unregisterComponent('btn');
        
        expect($state->getComponent('btn'))->toBeNull();
    });

    test('gets all registered components', function () {
        $state = StateManager::instance();
        
        $btn1 = new ButtonBuilder();
        $btn1->id('btn1');
        $btn2 = new ButtonBuilder();
        $btn2->id('btn2');
        
        $state->registerComponent('btn1', $btn1);
        $state->registerComponent('btn2', $btn2);
        
        $components = $state->getComponents();
        
        expect($components)->toBeArray();
        expect(count($components))->toBe(2);
        expect($components['btn1'])->toBe($btn1);
        expect($components['btn2'])->toBe($btn2);
    });

    test('sets event dispatcher', function () {
        $state = StateManager::instance();
        $dispatcher = new EventDispatcher();
        
        $state->setEventDispatcher($dispatcher);
        
        expect($state->getEventDispatcher())->toBe($dispatcher);
    });

    test('dispatches state change events', function () {
        $state = StateManager::instance();
        $dispatcher = new EventDispatcher();
        
        $capturedEvent = null;
        $dispatcher->on(StateChangeEvent::class, function (StateChangeEvent $event) use (&$capturedEvent) {
            $capturedEvent = $event;
        });
        
        $state->setEventDispatcher($dispatcher);
        $state->set('test', 'value');
        
        expect($capturedEvent)->not->toBeNull();
        expect($capturedEvent->getKey())->toBe('test');
        expect($capturedEvent->getNewValue())->toBe('value');
    });

    test('does not dispatch when no event dispatcher', function () {
        $state = StateManager::instance();
        
        // Should not throw error
        $state->set('test', 'value');
        
        expect($state->get('test'))->toBe('value');
    });

    test('handles complex value types', function () {
        $state = StateManager::instance();
        
        $state->set('array', ['a' => 1, 'b' => 2]);
        $state->set('object', (object)['field' => 'value']);
        $state->set('null', null);
        $state->set('bool', true);
        
        expect($state->get('array'))->toBe(['a' => 1, 'b' => 2]);
        expect($state->get('object'))->toBe((object)['field' => 'value']);
        expect($state->get('null'))->toBeNull();
        expect($state->get('bool'))->toBeTrue();
    });

    test('supports method chaining for set', function () {
        $state = StateManager::instance();
        
        $result = $state->set('a', 1);
        
        expect($result)->toBe($state);
    });

    test('supports method chaining for update', function () {
        $state = StateManager::instance();
        
        $result = $state->update(['a' => 1]);
        
        expect($result)->toBe($state);
    });

    test('handles empty update', function () {
        $state = StateManager::instance();
        
        $state->set('existing', 'value');
        $state->update([]);
        
        expect($state->get('existing'))->toBe('value');
    });

    test('clears listeners', function () {
        $state = StateManager::instance();
        
        $count = 0;
        $state->watch('test', function () use (&$count) {
            $count++;
        });
        
        $state->set('test', 'value');
        expect($count)->toBe(1);
        
        // Clear all listeners
        $state->clear();
        
        $state->set('test', 'new');
        expect($count)->toBe(1); // Still 1, listener was cleared
    });

    test('works with bound components', function () {
        $state = StateManager::instance();
        
        $entry = new EntryBuilder();
        $entry->id('username');
        $entry->bind('user_name');
        
        $state->registerComponent('username', $entry);
        $state->set('user_name', 'John');
        
        // Verify the component is registered
        expect($state->getComponent('username'))->toBe($entry);
        expect($state->get('user_name'))->toBe('John');
    });

    test('handles multiple state changes with listeners', function () {
        $state = StateManager::instance();
        
        $changes = [];
        $state->watch('counter', function ($new, $old) use (&$changes) {
            $changes[] = ['new' => $new, 'old' => $old];
        });
        
        $state->set('counter', 1);
        $state->set('counter', 2);
        $state->set('counter', 3);
        $state->set('counter', 3); // No change
        
        expect(count($changes))->toBe(3);
        expect($changes[0]['new'])->toBe(1);
        expect($changes[1]['new'])->toBe(2);
        expect($changes[2]['new'])->toBe(3);
    });
});
