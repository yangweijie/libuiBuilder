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
use Kingbes\Libui\View\Components\GroupBuilder;

/**
 * 视图构建器 - 所有组件的入口
 */
class Builder
{
    private ComponentBuilder $currentComponent;

    /**
     * 创建新的 Builder 实例用于链式调用
     */
    public static function create(): static
    {
        return new static();
    }

    /**
 * 设置当前组件(私有辅助方法)
     */
    private function setCurrent(ComponentBuilder $component): static
    {
        $this->currentComponent = $component;
        return $this;
    }

    /**
     * 获取最终构建的组件
     */
    public function get(): ComponentBuilder
    {
        return $this->currentComponent;
    }
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

    public static function group(array $config = []): GroupBuilder
    {
        return new GroupBuilder($config);
    }

    // ========== 链式调用辅助函数 ==========
    
    /**
     * 配置当前组件的通用属性
     */
    public function config(string $key, $value): static
    {
        if (isset($this->currentComponent)) {
            $this->currentComponent->setConfig($key, $value);
        }
        return $this;
    }

    /**
     * 为当前组件设置ID
     */
    public function withId(string $id): static
    {
        if (isset($this->currentComponent)) {
            $this->currentComponent->id($id);
        }
        return $this;
    }

    /**
     * 为当前组件绑定状态
     */
    public function bindTo(string $stateKey): static
    {
        if (isset($this->currentComponent)) {
            $this->currentComponent->bind($stateKey);
        }
        return $this;
    }

    /**
     * 为当前组件添加事件处理器
     */
    public function addEvent(string $event, callable $handler): static
    {
        if (isset($this->currentComponent)) {
            $this->currentComponent->on($event, $handler);
        }
        return $this;
    }

    /**
     * 添加子组件到当前容器
     */
    public function child(ComponentBuilder $child): static
    {
        if (isset($this->currentComponent)) {
            $this->currentComponent->addChild($child);
        }
        return $this;
    }

    /**
     * 批量添加子组件到当前容器
     */
    public function children(array $children): static
    {
        if (isset($this->currentComponent)) {
            $this->currentComponent->contains($children);
        }
        return $this;
    }

    // ========== 快速创建组件并设为当前 ==========
    
    /**
     * 创建窗口并设为当前组件
     */
    public function newWindow(array $config = []): static
    {
        return $this->setCurrent(new WindowBuilder($config));
    }

    /**
     * 创建垂直容器并设为当前组件
     */
    public function newVbox(array $config = []): static
    {
        return $this->setCurrent(new BoxBuilder('vertical', $config));
    }

    /**
     * 创建水平容器并设为当前组件
     */
    public function newHbox(array $config = []): static
    {
        return $this->setCurrent(new BoxBuilder('horizontal', $config));
    }

    /**
     * 创建网格并设为当前组件
     */
    public function newGrid(array $config = []): static
    {
        return $this->setCurrent(new GridBuilder($config));
    }

    /**
     * 创建按钮并设为当前组件
     */
    public function newButton(array $config = []): static
    {
        return $this->setCurrent(new ButtonBuilder($config));
    }

    /**
     * 创建标签并设为当前组件
     */
    public function newLabel(array $config = []): static
    {
        return $this->setCurrent(new LabelBuilder($config));
    }

    /**
     * 创建输入框并设为当前组件
     */
    public function newEntry(array $config = []): static
    {
        return $this->setCurrent(new EntryBuilder($config));
    }

    /**
     * 创建复选框并设为当前组件
     */
    public function newCheckbox(array $config = []): static
    {
        return $this->setCurrent(new CheckboxBuilder($config));
    }

    /**
     * 创建下拉框并设为当前组件
     */
    public function newCombobox(array $config = []): static
    {
        return $this->setCurrent(new ComboboxBuilder($config));
    }

    /**
     * 创建多行输入框并设为当前组件
     */
    public function newTextarea(array $config = []): static
    {
        return $this->setCurrent(new MultilineEntryBuilder($config));
    }

    /**
     * 创建滑块并设为当前组件
     */
    public function newSlider(array $config = []): static
    {
        return $this->setCurrent(new SliderBuilder($config));
    }

    /**
     * 创建进度条并设为当前组件
     */
    public function newProgressBar(array $config = []): static
    {
        return $this->setCurrent(new ProgressBarBuilder($config));
    }

    /**
     * 创建标签页并设为当前组件
     */
    public function newTab(array $config = []): static
    {
        return $this->setCurrent(new TabBuilder($config));
    }

    /**
     * 创建表格并设为当前组件
     */
    public function newTable(array $config = []): static
    {
        return $this->setCurrent(new TableBuilder($config));
    }

    /**
     * 创建画布并设为当前组件
     */
    public function newCanvas(array $config = []): static
    {
        return $this->setCurrent(new CanvasBuilder($config));
    }

    /**
     * 创建分隔符并设为当前组件
     */
    public function newSeparator(array $config = []): static
    {
        return $this->setCurrent(new SeparatorBuilder($config));
    }

    /**
     * 创建水平分隔符并设为当前组件
     */
    public function newHSeparator(array $config = []): static
    {
        return $this->setCurrent(new SeparatorBuilder(array_merge($config, ['orientation' => 'horizontal'])));
    }

    /**
     * 创建垂直分隔符并设为当前组件
     */
    public function newVSeparator(array $config = []): static
    {
        return $this->setCurrent(new SeparatorBuilder(array_merge($config, ['orientation' => 'vertical'])));
    }

    /**
     * 创建数字输入框并设为当前组件
     */
    public function newSpinbox(array $config = []): static
    {
        return $this->setCurrent(new SpinboxBuilder($config));
    }

    /**
     * 创建单选按钮组并设为当前组件
     */
    public function newRadio(array $config = []): static
    {
        return $this->setCurrent(new RadioBuilder($config));
    }

    /**
     * 创建密码输入框并设为当前组件
     */
    public function newPasswordEntry(array $config = []): static
    {
        return $this->setCurrent(new EntryBuilder(array_merge($config, ['password' => true])));
    }

    /**
     * 创建可编辑下拉框并设为当前组件
     */
    public function newEditableCombobox(array $config = []): static
    {
        return $this->setCurrent(new ComboboxBuilder(array_merge($config, ['editable' => true])));
    }

    /**
     * 创建分组控件并设为当前组件
     */
    public function newGroup(array $config = []): static
    {
        return $this->setCurrent(new GroupBuilder($config));
    }
}