<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Menu;
use Kingbes\Libui\MenuItem;
use FFI\CData;

class MenuBuilder
{
    private array $menus = [];

    public function menu(string $title): MenuItemBuilder
    {
        $menuBuilder = new MenuItemBuilder($title);
        $this->menus[] = $menuBuilder;
        return $menuBuilder;
    }

    public function build(): array
    {
        $nativeMenus = [];
        foreach ($this->menus as $menu) {
            $nativeMenus[] = $menu->build();
        }
        return $nativeMenus;
    }
}