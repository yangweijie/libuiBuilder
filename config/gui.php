<?php

use Kingbes\Libui\App;
use Kingbes\Libui\View\Template\BladeGuiRenderer;
use Kingbes\Libui\View\Template\XmlRenderer;

return [
    'paths' => [
        'views' => [
            __DIR__ . '/../app/views',
            __DIR__ . '/../resources/gui',
            __DIR__ . '/../components'
        ]
    ],

    'globals' => [
        'data' => [
            'app_name' => env('APP_NAME', 'LibUI Application'),
            'version' => '1.0.0',
            'debug' => env('DEBUG', false)
        ],

        'handlers' => [
            'quit' => function() {
                App::quit();
            }
        ]
    ],

    'engines' => [
        'xml' => XmlRenderer::class,
        'gui' => XmlRenderer::class,
        'blade.gui' => BladeGuiRenderer::class,
//        'twig.gui' => \App\Template\TwigGuiRenderer::class, // 用户自定义引擎
    ]
];