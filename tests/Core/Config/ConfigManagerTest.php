<?php

declare(strict_types=1);

namespace Tests\Core\Config;

use Kingbes\Libui\View\Core\Config\ConfigManager;
use InvalidArgumentException;

uses(\Tests\TestCase::class);

describe('ConfigManager', function () {
    test('can be instantiated with empty config', function () {
        $manager = new ConfigManager();
        expect($manager)->toBeInstanceOf(ConfigManager::class);
    });

    test('can be instantiated with initial config', function () {
        $config = [
            'app' => ['title' => 'Test App'],
            'builder' => ['auto_register' => true],
        ];
        
        $manager = new ConfigManager($config);
        expect($manager->get('app.title'))->toBe('Test App');
        expect($manager->get('builder.auto_register'))->toBeTrue();
    });

    test('gets nested config values', function () {
        $manager = new ConfigManager([
            'app' => [
                'title' => 'App',
                'width' => 800,
                'height' => 600,
            ],
        ]);
        
        expect($manager->get('app.title'))->toBe('App');
        expect($manager->get('app.width'))->toBe(800);
        expect($manager->get('app.height'))->toBe(600);
    });

    test('gets default value for non-existent key', function () {
        $manager = new ConfigManager();
        
        $result = $manager->get('nonexistent.key', 'default');
        expect($result)->toBe('default');
    });

    test('gets null for non-existent key without default', function () {
        $manager = new ConfigManager();
        
        $result = $manager->get('nonexistent.key');
        expect($result)->toBeNull();
    });

    test('sets config values', function () {
        $manager = new ConfigManager();
        
        $manager->set('app.title', 'New App');
        expect($manager->get('app.title'))->toBe('New App');
        
        $manager->set('app.width', 1024);
        expect($manager->get('app.width'))->toBe(1024);
    });

    test('sets nested config values', function () {
        $manager = new ConfigManager();
        
        $manager->set('events.enabled', true);
        $manager->set('events.namespace', 'test');
        
        expect($manager->get('events.enabled'))->toBeTrue();
        expect($manager->get('events.namespace'))->toBe('test');
    });

    test('has config key', function () {
        $manager = new ConfigManager([
            'app' => ['title' => 'Test'],
        ]);
        
        expect($manager->has('app.title'))->toBeTrue();
        expect($manager->has('app.width'))->toBeFalse();
        expect($manager->has('nonexistent'))->toBeFalse();
    });

    test('gets all config', function () {
        $config = [
            'app' => ['title' => 'Test'],
            'builder' => ['auto_register' => true],
        ];
        
        $manager = new ConfigManager($config);
        $all = $manager->all();
        
        expect($all)->toBeArray();
        expect($all['app']['title'])->toBe('Test');
        expect($all['builder']['auto_register'])->toBeTrue();
    });

    test('merges config', function () {
        $manager = new ConfigManager([
            'app' => ['title' => 'Old', 'width' => 800],
        ]);
        
        $manager->merge([
            'app' => ['title' => 'New'],
            'builder' => ['auto_register' => true],
        ]);
        
        expect($manager->get('app.title'))->toBe('New');
        expect($manager->get('app.width'))->toBe(800); // Preserved
        expect($manager->get('builder.auto_register'))->toBeTrue(); // Added
    });

    test('clears config', function () {
        $manager = new ConfigManager([
            'app' => ['title' => 'Test'],
        ]);
        
        expect($manager->has('app.title'))->toBeTrue();
        
        $manager->clear();
        
        expect($manager->has('app.title'))->toBeFalse();
        expect($manager->all())->toBeEmpty();
    });

    test('validates config with schema - valid values', function () {
        $manager = new ConfigManager([
            'app' => [
                'title' => 'Test App',
                'width' => 800,
                'height' => 600,
                'margined' => true,
            ],
            'builder' => [
                'auto_register' => true,
                'enable_logging' => false,
            ],
            'events' => [
                'enabled' => true,
                'namespace' => 'test',
            ],
        ]);
        
        expect($manager->get('app.title'))->toBe('Test App');
        expect($manager->get('app.width'))->toBe(800);
        expect($manager->get('app.margined'))->toBeTrue();
    });

    test('validates boolean values', function () {
        $manager = new ConfigManager([
            'app' => ['margined' => true],
            'builder' => ['auto_register' => false],
        ]);
        
        expect($manager->get('app.margined'))->toBeTrue();
        expect($manager->get('builder.auto_register'))->toBeFalse();
    });

    test('validates numeric values', function () {
        $manager = new ConfigManager([
            'app' => [
                'width' => 800,
                'height' => 600,
            ],
        ]);
        
        expect($manager->get('app.width'))->toBe(800);
        expect($manager->get('app.height'))->toBe(600);
    });

    test('validates string values', function () {
        $manager = new ConfigManager([
            'app' => ['title' => 'My App'],
            'events' => ['namespace' => 'my_namespace'],
        ]);
        
        expect($manager->get('app.title'))->toBe('My App');
        expect($manager->get('events.namespace'))->toBe('my_namespace');
    });

    test('validates array values', function () {
        $manager = new ConfigManager([
            'events' => ['global_listeners' => ['listener1', 'listener2']],
        ]);
        
        expect($manager->get('events.global_listeners'))->toBe(['listener1', 'listener2']);
    });

    test('handles default values from schema', function () {
        $manager = new ConfigManager([]);
        
        // These should get defaults from schema
        expect($manager->get('events.enabled'))->toBe(true);
        expect($manager->get('events.namespace'))->toBe('builder');
    });

    test('supports dot notation for nested access', function () {
        $manager = new ConfigManager([
            'level1' => [
                'level2' => [
                    'level3' => 'value',
                ],
            ],
        ]);
        
        expect($manager->get('level1.level2.level3'))->toBe('value');
    });

    test('supports array notation for nested access', function () {
        $manager = new ConfigManager([
            'app' => ['title' => 'Test'],
        ]);
        
        expect($manager->get('app.title'))->toBe('Test');
    });

    test('returns config manager instance', function () {
        $manager = new ConfigManager();
        expect($manager)->toBeInstanceOf(ConfigManager::class);
    });

    test('handles empty config gracefully', function () {
        $manager = new ConfigManager([]);
        
        expect($manager->all())->toBeArray();
        expect($manager->get('any.key'))->toBeNull();
    });

    test('overwrites existing values', function () {
        $manager = new ConfigManager([
            'app' => ['title' => 'Original'],
        ]);
        
        $manager->set('app.title', 'Updated');
        expect($manager->get('app.title'))->toBe('Updated');
    });

    test('creates nested structure automatically', function () {
        $manager = new ConfigManager();
        
        $manager->set('deeply.nested.config.value', 'test');
        
        expect($manager->get('deeply.nested.config.value'))->toBe('test');
    });

    test('handles boolean string conversions', function () {
        $manager = new ConfigManager([
            'builder' => [
                'auto_register' => true,
                'enable_logging' => false,
            ],
        ]);
        
        $autoReg = $manager->get('builder.auto_register');
        $logging = $manager->get('builder.enable_logging');
        
        expect($autoReg)->toBeTrue();
        expect($logging)->toBeFalse();
    });

    test('preserves integer values', function () {
        $manager = new ConfigManager([
            'app' => [
                'width' => 800,
                'height' => 600,
            ],
        ]);
        
        expect($manager->get('app.width'))->toBe(800);
        expect($manager->get('app.height'))->toBe(600);
        expect(is_int($manager->get('app.width')))->toBeTrue();
    });

    test('handles mixed config types', function () {
        $manager = new ConfigManager([
            'app' => [
                'title' => 'Test',
                'width' => 800,
                'margined' => true,
            ],
            'events' => [
                'enabled' => true,
                'namespace' => 'test',
                'global_listeners' => [],
            ],
        ]);
        
        expect($manager->get('app.title'))->toBeString();
        expect($manager->get('app.width'))->toBeInt();
        expect($manager->get('app.margined'))->toBeBool();
        expect($manager->get('events.global_listeners'))->toBeArray();
    });

    test('supports fluent interface', function () {
        $manager = new ConfigManager();
        
        $result = $manager->set('app.title', 'Test');
        
        expect($result)->toBe($manager);
    });
});
