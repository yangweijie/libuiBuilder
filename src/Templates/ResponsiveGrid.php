<?php

namespace Kingbes\Libui\View\Templates;


use Kingbes\Libui\View\ResponsiveGridBuilder;

class ResponsiveGrid
{
    public static function create(int $columns = 12): ResponsiveGridBuilder
    {
        return new ResponsiveGridBuilder($columns);
    }
}