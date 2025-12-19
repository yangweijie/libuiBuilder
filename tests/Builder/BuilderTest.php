<?php

declare(strict_types=1);

namespace Tests\Builder;

use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\Builder\ButtonBuilder;
use Kingbes\Libui\View\Builder\LabelBuilder;
use Kingbes\Libui\View\Builder\EntryBuilder;
use Kingbes\Libui\View\Builder\GridBuilder;
use Kingbes\Libui\View\Builder\BoxBuilder;
use Kingbes\Libui\View\Builder\TabBuilder;
use Kingbes\Libui\View\Builder\WindowBuilder;
use Kingbes\Libui\View\Builder\SliderBuilder;
use Kingbes\Libui\View\Builder\SpinboxBuilder;
use Kingbes\Libui\View\Builder\ProgressBarBuilder;
use Kingbes\Libui\View\Builder\CheckboxBuilder;
use Kingbes\Libui\View\Builder\ComboboxBuilder;
use Kingbes\Libui\View\Builder\SeparatorBuilder;
use Kingbes\Libui\View\Builder\GroupBuilder;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Core\Config\ConfigManager;
use Kingbes\Libui\View\Core\Event\EventDispatcher;

uses(\Tests\TestCase::class);

describe('Builder Factory Methods', function () {
    beforeEach(function () {
        StateManager::reset();
        Builder::setStateManager(null);
        Builder::setEventDispatcher(null);
        Builder::setConfigManager(null);
    });

    test('creates window builder', function () {
        $builder = Builder::window();
        expect($builder)->toBeInstanceOf(WindowBuilder::class);
    });

    test('creates button builder', function () {
        $builder = Builder::button();
        expect($builder)->toBeInstanceOf(ButtonBuilder::class);
    });

    test('creates label builder', function () {
        $builder = Builder::label();
        expect($builder)->toBeInstanceOf(LabelBuilder::class);
    });

    test('creates entry builder', function () {
        $builder = Builder::entry();
        expect($builder)->toBeInstanceOf(EntryBuilder::class);
    });

    test('creates grid builder', function () {
        $builder = Builder::grid();
        expect($builder)->toBeInstanceOf(GridBuilder::class);
    });

    test('creates hbox builder', function () {
        $builder = Builder::hbox();
        expect($builder)->toBeInstanceOf(BoxBuilder::class);
        expect($builder->getConfig()['direction'])->toBe('horizontal');
    });

    test('creates vbox builder', function () {
        $builder = Builder::vbox();
        expect($builder)->toBeInstanceOf(BoxBuilder::class);
        expect($builder->getConfig()['direction'])->toBe('vertical');
    });

    test('creates tab builder', function () {
        $builder = Builder::tab();
        expect($builder)->toBeInstanceOf(TabBuilder::class);
    });

    test('creates slider builder', function () {
        $builder = Builder::slider();
        expect($builder)->toBeInstanceOf(SliderBuilder::class);
    });

    test('creates spinbox builder', function () {
        $builder = Builder::spinbox();
        expect($builder)->toBeInstanceOf(SpinboxBuilder::class);
    });

    test('creates progress builder', function () {
        $builder = Builder::progress();
        expect($builder)->toBeInstanceOf(ProgressBarBuilder::class);
    });

    test('creates checkbox builder', function () {
        $builder = Builder::checkbox();
        expect($builder)->toBeInstanceOf(CheckboxBuilder::class);
    });

    test('creates combobox builder', function () {
        $builder = Builder::combobox();
        expect($builder)->toBeInstanceOf(ComboboxBuilder::class);
    });

    test('creates separator builder', function () {
        $builder = Builder::separator();
        expect($builder)->toBeInstanceOf(SeparatorBuilder::class);
    });

    test('creates group builder', function () {
        $builder = Builder::group();
        expect($builder)->toBeInstanceOf(GroupBuilder::class);
    });

    test('creates box with custom direction', function () {
        $builder = Builder::box('horizontal');
        expect($builder->getConfig()['direction'])->toBe('horizontal');
        
        $builder2 = Builder::box('vertical');
        expect($builder2->getConfig()['direction'])->toBe('vertical');
    });
});

describe('Builder Dependency Injection', function () {
    beforeEach(function () {
        StateManager::reset();
        Builder::setStateManager(null);
        Builder::setEventDispatcher(null);
        Builder::setConfigManager(null);
    });

    test('sets and gets state manager', function () {
        $stateManager = StateManager::instance();
        Builder::setStateManager($stateManager);
        
        expect(Builder::getStateManager())->toBe($stateManager);
    });

    test('sets and gets event dispatcher', function () {
        $eventDispatcher = new EventDispatcher();
        Builder::setEventDispatcher($eventDispatcher);
        
        expect(Builder::getEventDispatcher())->toBe($eventDispatcher);
    });

    test('sets and gets config manager', function () {
        $configManager = new ConfigManager(['app' => ['title' => 'Test']]);
        Builder::setConfigManager($configManager);
        
        expect(Builder::getConfigManager())->toBe($configManager);
    });

    test('injects dependencies into created builders', function () {
        $stateManager = StateManager::instance();
        $eventDispatcher = new EventDispatcher();
        $configManager = new ConfigManager(['app' => ['title' => 'Test']]);
        
        Builder::setStateManager($stateManager);
        Builder::setEventDispatcher($eventDispatcher);
        Builder::setConfigManager($configManager);
        
        $button = Builder::button();
        
        expect($button->getStateManager())->toBe($stateManager);
        expect($button->getEventDispatcher())->toBe($eventDispatcher);
        expect($button->getConfigManager())->toBe($configManager);
    });
});

describe('Builder Chainable Methods', function () {
    test('button chainable methods return self', function () {
        $button = Builder::button();
        
        expect($button->id('test'))->toBe($button);
        expect($button->text('Test'))->toBe($button);
        expect($button->onClick(function () {}))->toBe($button);
    });

    test('label chainable methods return self', function () {
        $label = Builder::label();
        
        expect($label->text('Test'))->toBe($label);
        expect($label->align('center'))->toBe($label);
        expect($label->id('test'))->toBe($label);
    });

    test('entry chainable methods return self', function () {
        $entry = Builder::entry();
        
        expect($entry->placeholder('Test'))->toBe($entry);
        expect($entry->bind('key'))->toBe($entry);
        expect($entry->password())->toBe($entry);
        expect($entry->onChange(function () {}))->toBe($entry);
    });

    test('window chainable methods return self', function () {
        $window = Builder::window();
        
        expect($window->title('Test'))->toBe($window);
        expect($window->size(800, 600))->toBe($window);
        expect($window->margined(true))->toBe($window);
        expect($window->resizable(true))->toBe($window);
    });

    test('grid chainable methods return self', function () {
        $grid = Builder::grid();
        
        expect($grid->columns(2))->toBe($grid);
        expect($grid->padded(true))->toBe($grid);
        expect($grid->append(Builder::label(), 0, 0))->toBe($grid);
    });

    test('box chainable methods return self', function () {
        $box = Builder::box();
        
        expect($box->direction('horizontal'))->toBe($box);
        expect($box->padded(true))->toBe($box);
        expect($box->append(Builder::button()))->toBe($box);
    });

    test('slider chainable methods return self', function () {
        $slider = Builder::slider();
        
        expect($slider->range(0, 100))->toBe($slider);
        expect($slider->value(50))->toBe($slider);
        expect($slider->bind('key'))->toBe($slider);
        expect($slider->onChange(function () {}))->toBe($slider);
    });

    test('checkbox chainable methods return self', function () {
        $checkbox = Builder::checkbox();
        
        expect($checkbox->text('Option'))->toBe($checkbox);
        expect($checkbox->checked(true))->toBe($checkbox);
        expect($checkbox->bind('key'))->toBe($checkbox);
        expect($checkbox->onChange(function () {}))->toBe($checkbox);
    });
});

describe('Builder Component Configuration', function () {
    test('button configuration', function () {
        $button = Builder::button()
            ->id('btn')
            ->text('Click')
            ->onClick(function () {});
        
        expect($button->getId())->toBe('btn');
        expect($button->getText())->toBe('Click');
        expect($button->getType())->toBe('button');
        expect(isset($button->getEvents()['onClick']))->toBeTrue();
    });

    test('label configuration', function () {
        $label = Builder::label()
            ->id('lbl')
            ->text('Hello')
            ->align('center', 'end');
        
        expect($label->getId())->toBe('lbl');
        expect($label->getText())->toBe('Hello');
        expect($label->getConfig()['align_horizontal'])->toBe('center');
        expect($label->getConfig()['align_vertical'])->toBe('end');
    });

    test('entry configuration', function () {
        $entry = Builder::entry()
            ->id('ent')
            ->placeholder('Enter text')
            ->bind('username')
            ->password()
            ->onChange(function () {});
        
        expect($entry->getId())->toBe('ent');
        expect($entry->getConfig()['placeholder'])->toBe('Enter text');
        expect($entry->getConfig()['bind'])->toBe('username');
        expect($entry->getConfig()['type'])->toBe('password');
        expect(isset($entry->getEvents()['onChange']))->toBeTrue();
    });

    test('slider configuration', function () {
        $slider = Builder::slider()
            ->id('sl')
            ->range(0, 100)
            ->value(50)
            ->bind('volume')
            ->onChange(function () {});
        
        expect($slider->getId())->toBe('sl');
        expect($slider->getConfig()['min'])->toBe(0);
        expect($slider->getConfig()['max'])->toBe(100);
        expect($slider->getConfig()['value'])->toBe(50);
        expect($slider->getConfig()['bind'])->toBe('volume');
    });

    test('window configuration', function () {
        $window = Builder::window()
            ->id('win')
            ->title('App')
            ->size(800, 600)
            ->margined(true)
            ->resizable(false)
            ->menubar(true);
        
        expect($window->getId())->toBe('win');
        expect($window->getConfig()['title'])->toBe('App');
        expect($window->getConfig()['width'])->toBe(800);
        expect($window->getConfig()['height'])->toBe(600);
        expect($window->getConfig()['margined'])->toBeTrue();
        expect($window->getConfig()['resizable'])->toBeFalse();
        expect($window->getConfig()['hasMenubar'])->toBeTrue();
    });

    test('grid configuration', function () {
        $grid = Builder::grid()
            ->columns(3)
            ->padded(true)
            ->append(Builder::label()->text('A'), 0, 0)
            ->append(Builder::label()->text('B'), 0, 1);
        
        expect($grid->getConfig()['columns'])->toBe(3);
        expect($grid->getConfig()['padded'])->toBeTrue();
        expect(count($grid->getItems()))->toBe(2);
    });

    test('box configuration', function () {
        $box = Builder::box('vertical')
            ->padded(true)
            ->append(Builder::button())
            ->append(Builder::label());
        
        expect($box->getConfig()['direction'])->toBe('vertical');
        expect($box->getConfig()['padded'])->toBeTrue();
        expect(count($box->getChildren()))->toBe(2);
    });

    test('tab configuration', function () {
        $tab = Builder::tab()
            ->tabs([
                'Tab 1' => Builder::label()->text('Content 1'),
                'Tab 2' => Builder::label()->text('Content 2'),
            ]);
        
        expect(count($tab->getConfig()['tabs']))->toBe(2);
    });

    test('group configuration', function () {
        $group = Builder::group()
            ->title('Group Title')
            ->margined(true)
            ->contains(Builder::label()->text('Inside'));
        
        expect($group->getConfig()['title'])->toBe('Group Title');
        expect($group->getConfig()['margined'])->toBeTrue();
        expect($group->getChildren())->toBeArray();
    });
});

describe('Builder Type Methods', function () {
    test('all builders return correct type', function () {
        expect(Builder::button()->getType())->toBe('button');
        expect(Builder::label()->getType())->toBe('label');
        expect(Builder::entry()->getType())->toBe('entry');
        expect(Builder::grid()->getType())->toBe('grid');
        expect(Builder::box()->getType())->toBe('box');
        expect(Builder::tab()->getType())->toBe('tab');
        expect(Builder::window()->getType())->toBe('window');
        expect(Builder::slider()->getType())->toBe('slider');
        expect(Builder::spinbox()->getType())->toBe('spinbox');
        expect(Builder::progress()->getType())->toBe('progress');
        expect(Builder::checkbox()->getType())->toBe('checkbox');
        expect(Builder::combobox()->getType())->toBe('combobox');
        expect(Builder::separator()->getType())->toBe('separator');
        expect(Builder::group()->getType())->toBe('group');
    });
});
