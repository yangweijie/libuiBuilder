<?php

namespace Kingbes\Libui\View;

use Throwable;

/**
 * 插件加载器 - 管理 libuiBuilder 插件的加载和注册
 */
class PluginLoader
{
    /**
     * 加载单个插件文件
     */
    public static function loadPlugin(string $pluginPath): bool
    {
        if (!file_exists($pluginPath)) {
            return false;
        }

        try {
            require_once $pluginPath;
            
            // 自动注册插件
            $className = basename($pluginPath, '.php');
            if (class_exists($className)) {
                if (method_exists($className, 'register')) {
                    $className::register();
                }
            }
            
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * 批量加载插件目录
     */
    public static function loadPlugins(string $pluginsDir): array
    {
        $loaded = [];
        $failed = [];
        
        if (!is_dir($pluginsDir)) {
            return ['loaded' => [], 'failed' => []];
        }
        
        foreach (glob($pluginsDir . '/*.php') as $pluginFile) {
            $pluginName = basename($pluginFile, '.php');
            if (self::loadPlugin($pluginFile)) {
                $loaded[] = $pluginName;
            } else {
                $failed[] = $pluginName;
            }
        }
        
        return ['loaded' => $loaded, 'failed' => $failed];
    }

    /**
     * 注册插件
     */
    public static function register(string $name, callable $callback): void
    {
        Builder::extend($name, $callback);
    }

    /**
     * 检查插件是否存在
     */
    public static function exists(string $pluginPath): bool
    {
        return file_exists($pluginPath);
    }

    /**
     * 获取插件信息
     */
    public static function getPluginInfo(string $pluginPath): array
    {
        if (!file_exists($pluginPath)) {
            return ['exists' => false];
        }

        $info = [
            'exists' => true,
            'path' => $pluginPath,
            'name' => basename($pluginPath, '.php'),
            'size' => filesize($pluginPath),
            'modified' => filemtime($pluginPath),
        ];

        // 尝试提取插件信息
        $content = file_get_contents($pluginPath);
        if (preg_match('/class\s+(\w+)/i', $content, $matches)) {
            $info['class'] = $matches[1];
        }

        return $info;
    }
}