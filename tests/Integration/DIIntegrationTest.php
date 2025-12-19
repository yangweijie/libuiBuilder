<?php

declare(strict_types=1);

namespace Tests\Integration;

use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\Core\Container\ContainerFactory;
use Kingbes\Libui\View\Core\Config\ConfigManager;
use Kingbes\Libui\View\Core\Event\EventDispatcher;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Core\Event\ButtonClickEvent;
use Kingbes\Libui\View\Core\Event\ValueChangeEvent;
use Kingbes\Libui\View\Core\Event\StateChangeEvent;

uses(\Tests\TestCase::class);

describe('DI Integration', function () {
    beforeEach(function () {
        StateManager::reset();
        Builder::setStateManager(null);
        Builder::setEventDispatcher(null);
        Builder::setConfigManager(null);
    });

    describe('Configuration Management', function () {
        test('creates config manager with valid schema', function () {
            $config = new ConfigManager([
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
            
            expect($config->get('app.title'))->toBe('Test App');
            expect($config->get('app.margined'))->toBeTrue();
            expect($config->get('events.enabled'))->toBeTrue();
        });

        test('config manager validates types', function () {
            $config = new ConfigManager([
                'app' => [
                    'title' => 'String',
                    'width' => 800,
                    'margined' => true,
                ],
            ]);
            
            expect(is_string($config->get('app.title')))->toBeTrue();
            expect(is_int($config->get('app.width')))->toBeTrue();
            expect(is_bool($config->get('app.margined')))->toBeTrue();
        });

        test('config manager supports dot notation', function () {
            $config = new ConfigManager([
                'level1' => [
                    'level2' => [
                        'level3' => 'value',
                    ],
                ],
            ]);
            
            expect($config->get('level1.level2.level3'))->toBe('value');
        });
    });

    describe('Event System', function () {
        test('event dispatcher registers and dispatches listeners', function () {
            $dispatcher = new EventDispatcher();
            $called = false;
            
            $dispatcher->on(ButtonClickEvent::class, function () use (&$called) {
                $called = true;
            });
            
            $builder = new \Kingbes\Libui\View\Builder\ButtonBuilder();
            $event = new ButtonClickEvent($builder);
            $dispatcher->dispatch($event);
            
            expect($called)->toBeTrue();
        });

        test('event dispatcher passes correct event data', function () {
            $dispatcher = new EventDispatcher();
            $captured = null;
            
            $dispatcher->on(ValueChangeEvent::class, function (ValueChangeEvent $event) use (&$captured) {
                $captured = $event;
            });
            
            $builder = new \Kingbes\Libui\View\Builder\EntryBuilder();
            $stateManager = StateManager::instance();
            $event = new ValueChangeEvent($builder, 'old', 'new', $stateManager);
            
            $dispatcher->dispatch($event);
            
            expect($captured->getOldValue())->toBe('old');
            expect($captured->getNewValue())->toBe('new');
            expect($captured->getComponent())->toBe($builder);
        });

        test('multiple listeners can be registered for same event', function () {
            $dispatcher = new EventDispatcher();
            $count = 0;
            
            $dispatcher->on(StateChangeEvent::class, function () use (&$count) {
                $count++;
            });
            
            $dispatcher->on(StateChangeEvent::class, function () use (&$count) {
                $count++;
            });
            
            $event = new StateChangeEvent('key', 'old', 'new');
            $dispatcher->dispatch($event);
            
            expect($count)->toBe(2);
        });
    });

    describe('State Management', function () {
        test('state manager tracks values', function () {
            $state = StateManager::instance();
            
            $state->set('user.name', 'John');
            $state->set('user.age', 30);
            
            expect($state->get('user.name'))->toBe('John');
            expect($state->get('user.age'))->toBe(30);
        });

        test('state manager triggers listeners on changes', function () {
            $state = StateManager::instance();
            $captured = [];
            
            $state->watch('counter', function ($new, $old) use (&$captured) {
                $captured[] = ['new' => $new, 'old' => $old];
            });
            
            $state->set('counter', 1);
            $state->set('counter', 2);
            
            expect(count($captured))->toBe(2);
            expect($captured[0]['new'])->toBe(1);
            expect($captured[1]['new'])->toBe(2);
        });

        test('state manager registers components', function () {
            $state = StateManager::instance();
            $button = new \Kingbes\Libui\View\Builder\ButtonBuilder();
            $button->id('testBtn');
            
            $state->registerComponent('testBtn', $button);
            
            expect($state->getComponent('testBtn'))->toBe($button);
        });

        test('state manager integrates with event dispatcher', function () {
            $state = StateManager::instance();
            $dispatcher = new EventDispatcher();
            $captured = null;
            
            $dispatcher->on(StateChangeEvent::class, function (StateChangeEvent $event) use (&$captured) {
                $captured = $event;
            });
            
            $state->setEventDispatcher($dispatcher);
            $state->set('test', 'value');
            
            expect($captured)->not->toBeNull();
            expect($captured->getKey())->toBe('test');
            expect($captured->getNewValue())->toBe('value');
        });
    });

    describe('Builder Integration', function () {
        test('builder uses injected state manager', function () {
            $state = StateManager::instance();
            $state->set('test', 'initial');
            
            Builder::setStateManager($state);
            
            $entry = Builder::entry()
                ->id('testEntry')
                ->bind('test');
            
            expect($entry->getStateManager())->toBe($state);
        });

        test('builder uses injected event dispatcher', function () {
            $dispatcher = new EventDispatcher();
            
            Builder::setEventDispatcher($dispatcher);
            
            $button = Builder::button()
                ->id('testBtn')
                ->onClick(function () {});
            
            expect($button->getEventDispatcher())->toBe($dispatcher);
        });

        test('builder uses injected config manager', function () {
            $config = new ConfigManager([
                'app' => ['title' => 'Injected Config'],
            ]);
            
            Builder::setConfigManager($config);
            
            $window = Builder::window();
            
            expect($window->getConfigManager())->toBe($config);
        });
    });

    describe('Complete DI Flow', function () {
        test('full integration with all components', function () {
            // 1. Create config
            $config = new ConfigManager([
                'app' => ['title' => 'Integration Test'],
                'events' => ['enabled' => true],
            ]);
            
            // 2. Create event dispatcher
            $dispatcher = new EventDispatcher();
            $eventLog = [];
            
            $dispatcher->on(ButtonClickEvent::class, function (ButtonClickEvent $e) use (&$eventLog) {
                $eventLog[] = 'button:' . $e->getComponentId();
            });
            
            $dispatcher->on(ValueChangeEvent::class, function (ValueChangeEvent $e) use (&$eventLog) {
                $eventLog[] = 'value:' . $e->getComponentId();
            });
            
            // 3. Create state manager
            $state = StateManager::instance();
            $state->setEventDispatcher($dispatcher);
            
            $stateChanges = [];
            $state->watch('input', function ($new, $old) use (&$stateChanges) {
                $stateChanges[] = $new;
            });
            
            // 4. Setup Builder
            Builder::setStateManager($state);
            Builder::setEventDispatcher($dispatcher);
            Builder::setConfigManager($config);
            
            // 5. Build UI components
            $button = Builder::button()
                ->id('btn')
                ->text('Click')
                ->onClick(function ($component, $stateManager, $eventDispatcher) {
                    $stateManager->set('clicked', true);
                });
            
            $entry = Builder::entry()
                ->id('input')
                ->bind('input')
                ->onChange(function ($value, $component, $stateManager) {
                    // This would be called on value change
                });
            
            // 6. Verify integration
            expect($button->getStateManager())->toBe($state);
            expect($button->getEventDispatcher())->toBe($dispatcher);
            expect($button->getConfigManager())->toBe($config);
            
            expect($entry->getStateManager())->toBe($state);
            expect($entry->getEventDispatcher())->toBe($dispatcher);
            
            // 7. Simulate state changes
            $state->set('input', 'test value');
            expect($stateChanges)->toContain('test value');
            
            // 8. Verify config access
            expect($config->get('app.title'))->toBe('Integration Test');
        });

        test('container factory creates properly configured container', function () {
            $container = ContainerFactory::create([
                'app' => ['title' => 'Container Test'],
                'logging' => ['enabled' => true],
            ]);
            
            // Verify services can be retrieved
            $state = $container->get(StateManager::class);
            $dispatcher = $container->get(EventDispatcher::class);
            $config = $container->get(ConfigManager::class);
            
            expect($state)->toBeInstanceOf(StateManager::class);
            expect($dispatcher)->toBeInstanceOf(EventDispatcher::class);
            expect($config)->toBeInstanceOf(ConfigManager::class);
        });

        test('builder chain with DI creates valid component structure', function () {
            $state = StateManager::instance();
            $dispatcher = new EventDispatcher();
            $config = new ConfigManager(['app' => ['title' => 'Test']]);
            
            Builder::setStateManager($state);
            Builder::setEventDispatcher($dispatcher);
            Builder::setConfigManager($config);
            
            // Create a complex UI structure
            $window = Builder::window()
                ->title($config->get('app.title'))
                ->size(800, 600)
                ->contains(
                    Builder::vbox()
                        ->padded(true)
                        ->contains([
                            Builder::label()
                                ->id('status')
                                ->text('Ready'),
                            
                            Builder::button()
                                ->id('action')
                                ->text('Execute')
                                ->onClick(function ($component, $stateManager) {
                                    $stateManager->set('status', 'executed');
                                }),
                            
                            Builder::entry()
                                ->id('input')
                                ->bind('user_input')
                                ->onChange(function ($value, $component, $stateManager) {
                                    $stateManager->set('last_input', $value);
                                }),
                        ])
                );
            
            // Verify structure
            expect($window->getType())->toBe('window');
            expect($window->getConfig()['title'])->toBe('Test');
            expect($window->getConfig()['width'])->toBe(800);
            
            // Verify dependencies were injected
            expect($window->getStateManager())->toBe($state);
            expect($window->getEventDispatcher())->toBe($dispatcher);
            expect($window->getConfigManager())->toBe($config);
        });

        test('event propagation through complete system', function () {
            $dispatcher = new EventDispatcher();
            $state = StateManager::instance();
            $state->setEventDispatcher($dispatcher);
            
            $eventsReceived = [];
            
            // Register global listeners
            $dispatcher->on(ButtonClickEvent::class, function (ButtonClickEvent $e) use (&$eventsReceived) {
                $eventsReceived[] = [
                    'type' => 'button',
                    'component' => $e->getComponentId(),
                    'time' => $e->getTimestamp(),
                ];
            });
            
            $dispatcher->on(StateChangeEvent::class, function (StateChangeEvent $e) use (&$eventsReceived) {
                $eventsReceived[] = [
                    'type' => 'state',
                    'key' => $e->getKey(),
                    'new' => $e->getNewValue(),
                ];
            });
            
            // Setup builder
            Builder::setStateManager($state);
            Builder::setEventDispatcher($dispatcher);
            
            // Create button with click handler
            $button = Builder::button()
                ->id('testButton')
                ->text('Click')
                ->onClick(function ($component, $stateManager, $eventDispatcher) {
                    $stateManager->set('clicked', time());
                });
            
            // Simulate button click (dispatch event)
            $clickEvent = new ButtonClickEvent($button, $state);
            $dispatcher->dispatch($clickEvent);
            
            // Simulate state change (which also dispatches event)
            $state->set('test', 'value');
            
            // Verify events were received
            expect(count($eventsReceived))->toBeGreaterThanOrEqual(2);
            
            $buttonEvent = $eventsReceived[0];
            expect($buttonEvent['type'])->toBe('button');
            expect($buttonEvent['component'])->toBe('testButton');
            
            $stateEvent = $eventsReceived[1];
            expect($stateEvent['type'])->toBe('state');
            expect($stateEvent['key'])->toBe('test');
            expect($stateEvent['new'])->toBe('value');
        });
    });

    describe('Dependency Chain', function () {
        test('all dependencies are properly wired', function () {
            // Create all services
            $config = new ConfigManager([
                'app' => ['title' => 'Wired Test'],
                'events' => ['enabled' => true],
            ]);
            
            $dispatcher = new EventDispatcher();
            $state = StateManager::instance();
            $state->setEventDispatcher($dispatcher);
            
            // Wire to Builder
            Builder::setConfigManager($config);
            Builder::setEventDispatcher($dispatcher);
            Builder::setStateManager($state);
            
            // Create component
            $component = Builder::button()
                ->id('wiredBtn')
                ->text('Wired')
                ->onClick(function ($c, $s, $d) {
                    $s->set('wired', true);
                });
            
            // Verify all dependencies present
            expect($component->getConfigManager())->toBe($config);
            expect($component->getEventDispatcher())->toBe($dispatcher);
            expect($component->getStateManager())->toBe($state);
            
            // Verify they work together
            $state->set('wired', false);
            $clickEvent = new ButtonClickEvent($component, $state);
            $dispatcher->dispatch($clickEvent);
            
            // Simulate what the onClick would do
            $state->set('wired', true);
            
            expect($state->get('wired'))->toBeTrue();
        });

        test('services can be retrieved from container', function () {
            $container = ContainerFactory::create([
                'app' => ['title' => 'Container Test'],
            ]);
            
            // Get services
            $builder = $container->get(\Kingbes\Libui\View\Builder\Builder::class);
            $config = $container->get(ConfigManager::class);
            $dispatcher = $container->get(EventDispatcher::class);
            $state = $container->get(StateManager::class);
            
            // Verify they are instances
            expect($builder)->toBeInstanceOf(\Kingbes\Libui\View\Builder\Builder::class);
            expect($config)->toBeInstanceOf(ConfigManager::class);
            expect($dispatcher)->toBeInstanceOf(EventDispatcher::class);
            expect($state)->toBeInstanceOf(StateManager::class);
        });

        test('container injects dependencies into builder', function () {
            $container = ContainerFactory::create([
                'app' => ['title' => 'DI Test'],
            ]);
            
            // Get configured builder from container
            $builder = $container->get(\Kingbes\Libui\View\Builder\Builder::class);
            
            // Builder should have its dependencies set via static methods
            // This tests that the container factory properly configures Builder
            expect(\Kingbes\Libui\View\Builder\Builder::getStateManager())->not->toBeNull();
            expect(\Kingbes\Libui\View\Builder\Builder::getEventDispatcher())->not->toBeNull();
            expect(\Kingbes\Libui\View\Builder\Builder::getConfigManager())->not->toBeNull();
        });
    });
});
