<?php

namespace Kingbes\Libui\View\Components;

use FFI\CData;
use Kingbes\Libui\Menu;
use Kingbes\Libui\MenuItem;

class MenuItemBuilder
{
    private string $title;
    private array $items = [];
    private ?CData $handle = null;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function item(string $text, ?callable $onClick = null): static
    {
        $this->items[] = [
            'type' => 'item',
            'text' => $text,
            'onClick' => $onClick,
        ];
        return $this;
    }

    public function checkItem(string $text, bool $checked = false, ?callable $onToggle = null): static
    {
        $this->items[] = [
            'type' => 'check',
            'text' => $text,
            'checked' => $checked,
            'onToggle' => $onToggle,
        ];
        return $this;
    }

    public function separator(): static
    {
        $this->items[] = ['type' => 'separator'];
        return $this;
    }

    public function quitItem(): static
    {
        $this->items[] = ['type' => 'quit'];
        return $this;
    }

    public function aboutItem(): static
    {
        $this->items[] = ['type' => 'about'];
        return $this;
    }

    public function preferencesItem(): static
    {
        $this->items[] = ['type' => 'preferences'];
        return $this;
    }

    public function submenu(string $text): SubMenuBuilder
    {
        $submenu = new SubMenuBuilder($text);
        $this->items[] = [
            'type' => 'submenu',
            'submenu' => $submenu,
        ];
        return $submenu;
    }

    public function build(): CData
    {
        if ($this->handle === null) {
            $this->handle = Menu::create($this->title);

            foreach ($this->items as $item) {
                $this->addMenuItem($item);
            }
        }
        return $this->handle;
    }

    private function addMenuItem(array $item): void
    {
        switch ($item['type']) {
            case 'item':
                $menuItem = Menu::appendItem($this->handle, $item['text']);
                if ($item['onClick']) {
                    MenuItem::onClicked($menuItem, $item['onClick']);
                }
                break;

            case 'check':
                $menuItem = Menu::appendCheckItem($this->handle, $item['text']);
                if ($item['checked']) {
                    MenuItem::setChecked($menuItem, true);
                }
                if ($item['onToggle']) {
                    MenuItem::onClicked($menuItem, $item['onToggle']);
                }
                break;

            case 'separator':
                Menu::appendSeparator($this->handle);
                break;

            case 'quit':
                Menu::appendQuitItem($this->handle);
                break;

            case 'about':
                Menu::appendAboutItem($this->handle);
                break;

            case 'preferences':
                Menu::appendPreferencesItem($this->handle);
                break;
        }
    }
}
