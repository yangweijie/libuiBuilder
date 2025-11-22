<?php

namespace Kingbes\Libui\View;

use Kingbes\Libui\View\Builder\TabBuilder;
use Kingbes\Libui\View\Components\CanvasBuilder;
use Kingbes\Libui\View\Components\CheckboxBuilder;
use Kingbes\Libui\View\Components\ComboboxBuilder;
use Kingbes\Libui\View\Components\GridBuilder;
use Kingbes\Libui\View\Components\MenuBuilder;
use Kingbes\Libui\View\Components\MultilineEntryBuilder;
use Kingbes\Libui\View\Components\ProgressBarBuilder;
use Kingbes\Libui\View\Components\RadioBuilder;
use Kingbes\Libui\View\Components\SeparatorBuilder;
use Kingbes\Libui\View\Components\SliderBuilder;
use Kingbes\Libui\View\Components\SpinboxBuilder;
use Kingbes\Libui\View\Components\TableBuilder;
use Kingbes\Libui\View\Components\WindowBuilder;
use Kingbes\Libui\View\Components\BoxBuilder;
use Kingbes\Libui\View\Components\ButtonBuilder;
use Kingbes\Libui\View\Components\LabelBuilder;
use Kingbes\Libui\View\Components\EntryBuilder;

/**
 * 视图构建器 - 所有组件的入口
 */
class Builder
{
    // 窗口组件
    public static function window(array $config = []): WindowBuilder
    {
        return new WindowBuilder($config);
    }

    // 容器组件
    public static function vbox(array $config = []): BoxBuilder
    {
        return new BoxBuilder('vertical', $config);
    }

    public static function hbox(array $config = []): BoxBuilder
    {
        return new BoxBuilder('horizontal', $config);
    }

    public static function grid(array $config = []): GridBuilder
    {
        return new GridBuilder($config);
    }

    public static function tab(array $config = []): TabBuilder
    {
        return new TabBuilder($config);
    }

    // 控件组件
    public static function button(array $config = []): ButtonBuilder
    {
        return new ButtonBuilder($config);
    }

    public static function label(array $config = []): LabelBuilder
    {
        return new LabelBuilder($config);
    }

    public static function entry(array $config = []): EntryBuilder
    {
        return new EntryBuilder($config);
    }

    public static function checkbox(array $config = []): CheckboxBuilder
    {
        return new CheckboxBuilder($config);
    }

    public static function combobox(array $config = []): ComboboxBuilder
    {
        return new ComboboxBuilder($config);
    }

// 便捷方法
    public static function passwordEntry(array $config = []): EntryBuilder
    {
        return new EntryBuilder(array_merge($config, ['password' => true]));
    }

    public static function editableCombobox(array $config = []): ComboboxBuilder
    {
        return new ComboboxBuilder(array_merge($config, ['editable' => true]));
    }

    public static function table(array $config = []): TableBuilder
    {
        return new TableBuilder($config);
    }

    public static function menu(): MenuBuilder
    {
        return new MenuBuilder();
    }

    public static function canvas(array $config = []): CanvasBuilder
    {
        return new CanvasBuilder($config);
    }

    public static function separator(): SeparatorBuilder
    {
        return new SeparatorBuilder();
    }

    // 便捷方法
    public static function hSeparator(array $config = []): SeparatorBuilder
    {
        return new SeparatorBuilder(array_merge($config, ['orientation' => 'horizontal']));
    }

    public static function vSeparator(array $config = []): SeparatorBuilder
    {
        return new SeparatorBuilder(array_merge($config, ['orientation' => 'vertical']));
    }

    public static function multilineEntry(array $config = []): MultilineEntryBuilder
    {
        return new MultilineEntryBuilder($config);
    }

    public static function textarea(array $config = []): MultilineEntryBuilder
    {
        return new MultilineEntryBuilder($config);
    }

    public static function spinbox(array $config = []): SpinboxBuilder
    {
        return new SpinboxBuilder($config);
    }

    public static function slider(array $config = []): SliderBuilder
    {
        return new SliderBuilder($config);
    }

    public static function progressBar(array $config = []): ProgressBarBuilder
    {
        return new ProgressBarBuilder($config);
    }

    public static function radio(array $config = []): RadioBuilder
    {
        return new RadioBuilder($config);
    }
}