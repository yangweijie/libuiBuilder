<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Core\Event\EventDispatcher;
use Kingbes\Libui\View\Core\Config\ConfigManager;

/**
 * 构建器工厂类
 * 
 * 提供静态方法创建各种组件构建器
 * 支持依赖注入和事件系统
 */
class Builder
{
    /** @var StateManager|null 全局状态管理器 */
    private static ?StateManager $stateManager = null;

    /** @var EventDispatcher|null 全局事件分发器 */
    private static ?EventDispatcher $eventDispatcher = null;

    /** @var ConfigManager|null 全局配置管理器 */
    private static ?ConfigManager $configManager = null;

    /**
     * 设置全局状态管理器
     *
     * @param StateManager|null $manager
     * @return void
     */
    public static function setStateManager(?StateManager $manager): void
    {
        self::$stateManager = $manager;
    }

    /**
     * 获取全局状态管理器
     *
     * @return StateManager|null
     */
    public static function getStateManager(): ?StateManager
    {
        return self::$stateManager;
    }

    /**
     * 设置全局事件分发器
     *
     * @param EventDispatcher $dispatcher
     * @return void
     */
    public static function setEventDispatcher(EventDispatcher $dispatcher): void
    {
        self::$eventDispatcher = $dispatcher;
    }

    /**
     * 获取全局事件分发器
     *
     * @return EventDispatcher|null
     */
    public static function getEventDispatcher(): ?EventDispatcher
    {
        return self::$eventDispatcher;
    }

    /**
     * 设置全局配置管理器
     *
     * @param ConfigManager $manager
     * @return void
     */
    public static function setConfigManager(ConfigManager $manager): void
    {
        self::$configManager = $manager;
    }

    /**
     * 获取全局配置管理器
     *
     * @return ConfigManager|null
     */
    public static function getConfigManager(): ?ConfigManager
    {
        return self::$configManager;
    }

    /**
     * 应用依赖注入到构建器
     *
     * @param ComponentBuilder $builder
     * @return ComponentBuilder
     */
    private static function applyDependencies(ComponentBuilder $builder): ComponentBuilder
    {
        if (self::$stateManager) {
            $builder->setStateManager(self::$stateManager);
        }
        if (self::$eventDispatcher) {
            $builder->setEventDispatcher(self::$eventDispatcher);
        }
        if (self::$configManager) {
            $builder->setConfigManager(self::$configManager);
        }
        return $builder;
    }

    /**
     * 创建窗口构建器
     *
     * @return WindowBuilder
     */
    public static function window(): WindowBuilder
    {
        return self::applyDependencies(new WindowBuilder());
    }

    /**
     * 创建按钮构建器
     *
     * @return ButtonBuilder
     */
    public static function button(): ButtonBuilder
    {
        return self::applyDependencies(new ButtonBuilder());
    }

    /**
     * 创建标签构建器
     *
     * @return LabelBuilder
     */
    public static function label(): LabelBuilder
    {
        return self::applyDependencies(new LabelBuilder());
    }

    /**
     * 创建输入框构建器
     *
     * @return EntryBuilder
     */
    public static function entry(): EntryBuilder
    {
        return self::applyDependencies(new EntryBuilder());
    }

    /**
     * 创建水平盒子构建器
     *
     * @return BoxBuilder
     */
    public static function hbox(): BoxBuilder
    {
        return self::applyDependencies(new BoxBuilder('horizontal'));
    }

    /**
     * 创建垂直盒子构建器
     *
     * @return BoxBuilder
     */
    public static function vbox(): BoxBuilder
    {
        return self::applyDependencies(new BoxBuilder('vertical'));
    }

    /**
     * 创建盒子构建器（指定方向）
     *
     * @param string $direction 方向 ('horizontal' 或 'vertical')
     * @return BoxBuilder
     */
    public static function box(string $direction = 'vertical'): BoxBuilder
    {
        return self::applyDependencies(new BoxBuilder($direction));
    }

    /**
     * 创建网格构建器
     *
     * @return GridBuilder
     */
    public static function grid(): GridBuilder
    {
        return self::applyDependencies(new GridBuilder());
    }

    /**
     * 创建标签页构建器
     *
     * @return TabBuilder
     */
    public static function tab(): TabBuilder
    {
        return self::applyDependencies(new TabBuilder());
    }

    /**
     * 创建表格构建器
     *
     * @return TableBuilder
     */
    public static function table(): TableBuilder
    {
        return self::applyDependencies(new TableBuilder());
    }

    /**
     * 创建复选框构建器
     *
     * @return CheckboxBuilder
     */
    public static function checkbox(): CheckboxBuilder
    {
        return self::applyDependencies(new CheckboxBuilder());
    }

    /**
     * 创建组合框构建器
     *
     * @return ComboboxBuilder
     */
    public static function combobox(): ComboboxBuilder
    {
        return self::applyDependencies(new ComboboxBuilder());
    }

    /**
     * 创建分隔线构建器
     *
     * @return SeparatorBuilder
     */
    public static function separator(): SeparatorBuilder
    {
        return self::applyDependencies(new SeparatorBuilder());
    }

    /**
     * 创建进度条构建器
     *
     * @return ProgressBarBuilder
     */
    public static function progress(): ProgressBarBuilder
    {
        return self::applyDependencies(new ProgressBarBuilder());
    }

    /**
     * 创建滑块构建器
     *
     * @return SliderBuilder
     */
    public static function slider(): SliderBuilder
    {
        return self::applyDependencies(new SliderBuilder());
    }

    /**
     * 创建数字输入框构建器
     *
     * @return SpinboxBuilder
     */
    public static function spinbox(): SpinboxBuilder
    {
        return self::applyDependencies(new SpinboxBuilder());
    }

    /**
     * 创建组容器构建器
     *
     * @return GroupBuilder
     */
    public static function group(): GroupBuilder
    {
        return self::applyDependencies(new GroupBuilder());
    }
}
