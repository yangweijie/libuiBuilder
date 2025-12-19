<?php

declare(strict_types=1);

/**
 * 配置文件示例
 * 
 * 用于 league/config 的配置管理
 * 支持 PHP、JSON、YAML 格式
 */

return [
    // 应用配置
    'app' => [
        'title' => 'libui Builder - DI 集成示例',
        'width' => 600,
        'height' => 500,
        'margined' => true,
    ],

    // Builder 配置
    'builder' => [
        'auto_register' => true,      // 自动注册组件到状态管理器
        'enable_logging' => true,     // 启用日志
        'default_state_manager' => 'default',
    ],

    // 事件系统配置
    'events' => [
        'enabled' => true,
        'namespace' => 'builder.integrated',
        'global_listeners' => [
            'ButtonClickEvent' => true,
            'ValueChangeEvent' => true,
            'StateChangeEvent' => true,
        ],
    ],

    // 日志配置
    'logging' => [
        'level' => 'info',            // debug, info, warning, error
        'path' => __DIR__ . '/../../logs/builder.log',
    ],

    // 依赖注入容器配置
    'dependencies' => [
        // 可以在这里添加自定义的服务绑定
        // 'MyCustomService' => function($container) {
        //     return new MyCustomService($container->get(ConfigManager::class));
        // },
    ],
];