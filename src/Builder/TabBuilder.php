<?php

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Tab;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Validation\ComponentBuilder;

class TabBuilder extends ComponentBuilder
{
    private array $tabs = [];

    public function getDefaultConfig(): array
    {
        return [
            'selectedIndex' => 0,
            'onTabSelected' => null,
        ];
    }

    protected function createNativeControl(): CData
    {
        return Tab::create();
    }

    protected function applyConfig(): void
    {
        // 设置初始选中的标签页
        $selectedIndex = $this->getConfig('selectedIndex');
        if ($selectedIndex > 0 && $selectedIndex < count($this->tabs)) {
            Tab::setSelected($this->handle, $selectedIndex);
        }

        // 绑定标签页切换事件
        if ($onTabSelected = $this->getConfig('onTabSelected')) {
            Tab::onSelected($this->handle, $onTabSelected);
        }
    }

    protected function canHaveChildren(): bool
    {
        return true;
    }

    protected function buildChildren(): void
    {
        foreach ($this->tabs as $tab) {
            $tabContent = $tab['content'];

            // 如果内容是数组，自动包装在垂直盒子中
            if (is_array($tabContent)) {
                $vbox = Builder::vbox();
                foreach ($tabContent as $component) {
                    $vbox->addChild($component);
                }
                $tabContent = $vbox;
            }

            Tab::append($this->handle, $tab['title'], $tabContent->build());
        }
    }

    // 添加标签页
    public function tab(string $title, $content): static
    {
        $this->tabs[] = [
            'title' => $title,
            'content' => $content
        ];
        return $this;
    }

    // 批量添加标签页
    public function tabs(array $tabs): static
    {
        foreach ($tabs as $title => $content) {
            $this->tab($title, $content);
        }
        return $this;
    }

    // 设置默认选中的标签页
    public function selected(int $index): static
    {
        return $this->setConfig('selectedIndex', $index);
    }

    // 绑定标签页切换事件
    public function onTabSelected(callable $callback): static
    {
        return $this->setConfig('onTabSelected', $callback);
    }

    // 获取当前选中的标签页索引
    public function getSelected(): int
    {
        return Tab::selected($this->handle);
    }

    // 程序化切换标签页
    public function switchTo(int $index): void
    {
        Tab::setSelected($this->handle, $index);
    }
}