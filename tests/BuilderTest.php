<?php

declare(strict_types=1);

namespace Tests;

use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\Builder\ButtonBuilder;
use Kingbes\Libui\View\Builder\LabelBuilder;
use Kingbes\Libui\View\Builder\EntryBuilder;
use Kingbes\Libui\View\Builder\GridBuilder;
use Kingbes\Libui\View\Builder\BoxBuilder;
use Kingbes\Libui\View\Builder\TabBuilder;
use Kingbes\Libui\View\Builder\WindowBuilder;
use Kingbes\Libui\View\State\StateManager;
use PHPUnit\Framework\TestCase;

/**
 * 构建器测试类
 */
class BuilderTest extends TestCase
{
    protected function setUp(): void
    {
        // 重置状态管理器
        StateManager::reset();
    }

    public function testWindowBuilder(): void
    {
        $window = Builder::window()
            ->title('Test Window')
            ->size(800, 600)
            ->margined(true)
            ->resizable(true);

        $this->assertInstanceOf(WindowBuilder::class, $window);
        $this->assertEquals('Test Window', $window->getConfig()['title']);
        $this->assertEquals(800, $window->getConfig()['width']);
        $this->assertEquals(600, $window->getConfig()['height']);
    }

    public function testButtonBuilder(): void
    {
        $button = Builder::button()
            ->id('testBtn')
            ->text('Click Me')
            ->onClick(function() {
                return 'clicked';
            });

        $this->assertInstanceOf(ButtonBuilder::class, $button);
        $this->assertEquals('testBtn', $button->getId());
        $this->assertEquals('Click Me', $button->getText());
        $this->assertArrayHasKey('onClick', $button->getEvents());
    }

    public function testLabelBuilder(): void
    {
        $label = Builder::label()
            ->id('testLabel')
            ->text('Hello World')
            ->align('center', 'center');

        $this->assertInstanceOf(LabelBuilder::class, $label);
        $this->assertEquals('testLabel', $label->getId());
        $this->assertEquals('Hello World', $label->getText());
        $this->assertEquals('center', $label->getConfig()['align_horizontal']);
    }

    public function testEntryBuilder(): void
    {
        $entry = Builder::entry()
            ->id('testEntry')
            ->placeholder('Enter text')
            ->bind('username')
            ->password();

        $this->assertInstanceOf(EntryBuilder::class, $entry);
        $this->assertEquals('testEntry', $entry->getId());
        $this->assertEquals('Enter text', $entry->getConfig()['placeholder']);
        $this->assertEquals('username', $entry->getConfig()['bind']);
        $this->assertEquals('password', $entry->getConfig()['type']);
    }

    public function testGridBuilder(): void
    {
        $grid = Builder::grid()
            ->columns(2)
            ->padded(true)
            ->append(Builder::label()->text('Name:'), 0, 0)
            ->append(Builder::entry(), 0, 1);

        $this->assertInstanceOf(GridBuilder::class, $grid);
        $this->assertEquals(2, $grid->getConfig()['columns']);
        $this->assertArrayHasKey('padded', $grid->getConfig());
        $this->assertCount(2, $grid->getItems());
    }

    public function testBoxBuilder(): void
    {
        $box = Builder::hbox()
            ->padded(true)
            ->append(Builder::button()->text('OK'), true)
            ->append(Builder::button()->text('Cancel'), false);

        $this->assertInstanceOf(BoxBuilder::class, $box);
        $this->assertEquals('horizontal', $box->getConfig()['direction']);
        $this->assertCount(2, $box->getChildren());
    }

    public function testTabBuilder(): void
    {
        $tab = Builder::tab()
            ->tabs([
                'Tab 1' => Builder::label()->text('Content 1'),
                'Tab 2' => Builder::label()->text('Content 2'),
            ]);

        $this->assertInstanceOf(TabBuilder::class, $tab);
        $this->assertCount(2, $tab->getConfig()['tabs'] ?? []);
    }

    public function testStateManagerIntegration(): void
    {
        $state = StateManager::instance();
        $state->set('testValue', 'initial');

        Builder::setStateManager($state);

        $entry = Builder::entry()
            ->id('boundEntry')
            ->bind('testValue');

        // 验证状态管理器已设置
        $this->assertEquals($state, Builder::getStateManager());
        
        // 验证构建器有状态管理器
        $this->assertNotNull($entry->getStateManager());
    }

    public function testChainableMethods(): void
    {
        // 验证所有方法都返回 $this
        $button = Builder::button();
        $this->assertSame($button, $button->id('test'));
        $this->assertSame($button, $button->text('Test'));
        
        $label = Builder::label();
        $this->assertSame($label, $label->text('Test'));
        $this->assertSame($label, $label->align('center'));
        
        $entry = Builder::entry();
        $this->assertSame($entry, $entry->placeholder('Test'));
        $this->assertSame($entry, $entry->bind('test'));
        
        $grid = Builder::grid();
        $this->assertSame($grid, $grid->columns(2));
        $this->assertSame($grid, $grid->padded(true));
        
        $box = Builder::box();
        $this->assertSame($box, $box->direction('horizontal'));
        $this->assertSame($box, $box->padded(true));
    }

    public function testFactoryMethods(): void
    {
        $this->assertInstanceOf(WindowBuilder::class, Builder::window());
        $this->assertInstanceOf(ButtonBuilder::class, Builder::button());
        $this->assertInstanceOf(LabelBuilder::class, Builder::label());
        $this->assertInstanceOf(EntryBuilder::class, Builder::entry());
        $this->assertInstanceOf(GridBuilder::class, Builder::grid());
        $this->assertInstanceOf(BoxBuilder::class, Builder::hbox());
        $this->assertInstanceOf(BoxBuilder::class, Builder::vbox());
        $this->assertInstanceOf(TabBuilder::class, Builder::tab());
    }

    public function testComponentTypes(): void
    {
        $this->assertEquals('button', Builder::button()->getType());
        $this->assertEquals('label', Builder::label()->getType());
        $this->assertEquals('entry', Builder::entry()->getType());
        $this->assertEquals('grid', Builder::grid()->getType());
        $this->assertEquals('box', Builder::box()->getType());
        $this->assertEquals('tab', Builder::tab()->getType());
    }

    public function testStateManagerStateManagement(): void
    {
        $state = StateManager::instance();
        
        // 测试设置和获取
        $state->set('name', 'Test');
        $this->assertEquals('Test', $state->get('name'));
        
        // 测试存在性检查
        $this->assertTrue($state->has('name'));
        $this->assertFalse($state->has('nonexistent'));
        
        // 测试批量更新
        $state->update(['a' => 1, 'b' => 2]);
        $this->assertEquals(1, $state->get('a'));
        $this->assertEquals(2, $state->get('b'));
        
        // 测试删除
        $state->delete('a');
        $this->assertFalse($state->has('a'));
        
        // 测试获取所有
        $all = $state->getAll();
        $this->assertArrayHasKey('b', $all);
    }

    public function testStateManagerListeners(): void
    {
        $state = StateManager::instance();
        
        $triggered = false;
        $newValue = null;
        $oldValue = null;
        
        $state->watch('test', function($new, $old) use (&$triggered, &$newValue, &$oldValue) {
            $triggered = true;
            $newValue = $new;
            $oldValue = $old;
        });
        
        $state->set('test', 'initial');
        $state->set('test', 'updated');
        
        $this->assertTrue($triggered);
        $this->assertEquals('updated', $newValue);
        $this->assertEquals('initial', $oldValue);
    }

    public function testStateManagerComponentRegistration(): void
    {
        $state = StateManager::instance();
        
        $button = Builder::button()->id('testBtn');
        $state->registerComponent('testBtn', $button);
        
        $retrieved = $state->getComponent('testBtn');
        $this->assertSame($button, $retrieved);
    }
}
