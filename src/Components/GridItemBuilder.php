<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\Align;
use Kingbes\Libui\View\Validation\ComponentBuilder;

class GridItemBuilder
{
    private array $config;

    public function __construct(ComponentBuilder $component, int $left, int $top,
                                int $xspan = 1, int $yspan = 1)
    {
        $this->config = [
            'component' => $component,
            'left' => $left,
            'top' => $top,
            'xspan' => $xspan,
            'yspan' => $yspan,
            'hexpand' => false,
            'vexpand' => false,
            'halign' => Align::Fill,
            'valign' => Align::Fill,
        ];
    }

    public function span(int $cols, int $rows = 1): static
    {
        $this->config['xspan'] = $cols;
        $this->config['yspan'] = $rows;
        return $this;
    }

    public function expand(bool $horizontal = true, bool $vertical = false): static
    {
        $this->config['hexpand'] = $horizontal;
        $this->config['vexpand'] = $vertical;
        return $this;
    }

    public function align(string $horizontal = 'fill', string $vertical = 'fill'): static
    {
        $alignMap = [
            'fill' => Align::Fill,
            'start' => Align::Start,
            'center' => Align::Center,
            'end' => Align::End
        ];

        $this->config['halign'] = $alignMap[$horizontal] ?? Align::Fill;
        $this->config['valign'] = $alignMap[$vertical] ?? Align::Fill;
        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}