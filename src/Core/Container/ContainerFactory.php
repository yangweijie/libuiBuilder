<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Core\Container;

use DI\Container;
use DI\ContainerBuilder;
use Kingbes\Libui\View\Core\Config\ConfigManager;
use Kingbes\Libui\View\Core\Event\EventDispatcher;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Builder\Builder;

/**
 * 依赖注入容器工厂
 * 统一管理所有服务的依赖注入配置
 */
class ContainerFactory
{
    private static ?Container $instance = null;

    /**
     * 创建并配置 DI 容器
     *
     * @param array $userConfig 用户自定义配置
     * @param bool $useCache 是否使用编译缓存
     * @return Container
     */
    public static function create(array $userConfig = [], bool $useCache = false): Container
    {
        if (self::$instance !== null && !$useCache) {
            return self::$instance;
        }

        $builder = new ContainerBuilder();
        
        // 定义所有服务
        $definitions = [
            // 配置管理器
            ConfigManager::class => function() use ($userConfig) {
                return new ConfigManager($userConfig);
            },

            // 事件分发器
            EventDispatcher::class => function() {
                return new EventDispatcher();
            },

            // 状态管理器（单例）
            StateManager::class => function() {
                return StateManager::instance();
            },

            // Builder 工厂（需要其他服务注入）
            Builder::class => function(Container $c) {
                $stateManager = $c->get(StateManager::class);
                $eventDispatcher = $c->get(EventDispatcher::class);
                $config = $c->get(ConfigManager::class);
                
                Builder::setStateManager($stateManager);
                Builder::setEventDispatcher($eventDispatcher);
                Builder::setConfigManager($config);
                
                return new Builder();
            },

            // 日志服务（可选）
            'Logger' => function(Container $c) {
                $config = $c->get(ConfigManager::class);
                if ($config->get('logging.enabled', false)) {
                    $path = $config->get('logging.path', 'logs/builder.log');
                    $level = $config->get('logging.level', 'info');
                    
                    // 简单的文件日志实现
                    return new class($path, $level) {
                        private $path;
                        private $level;
                        private $levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];
                        
                        public function __construct($path, $level) {
                            $this->path = $path;
                            $this->level = $level;
                        }
                        
                        public function log($level, $message, array $context = []) {
                            if ($this->levels[$level] >= $this->levels[$this->level]) {
                                $dir = dirname($this->path);
                                if (!is_dir($dir)) {
                                    mkdir($dir, 0755, true);
                                }
                                $timestamp = date('Y-m-d H:i:s');
                                $msg = "[$timestamp] [$level] $message" . PHP_EOL;
                                file_put_contents($this->path, $msg, FILE_APPEND);
                            }
                        }
                        
                        public function info($message) { $this->log('info', $message); }
                        public function warning($message) { $this->log('warning', $message); }
                        public function error($message) { $this->log('error', $message); }
                        public function debug($message) { $this->log('debug', $message); }
                    };
                }
                
                return null;
            },
        ];

        // 合并配置
        $builder->addDefinitions($definitions);
        
        // 用户自定义绑定
        if (!empty($userConfig['dependencies'] ?? null)) {
            $builder->addDefinitions($userConfig['dependencies']);
        }

        // 缓存配置
        if ($useCache) {
            $cacheDir = sys_get_temp_dir() . '/libui_builder_di_cache';
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            $builder->enableCompilation($cacheDir);
        }

        self::$instance = $builder->build();
        return self::$instance;
    }

    /**
     * 获取容器实例
     *
     * @return Container|null
     */
    public static function getInstance(): ?Container
    {
        return self::$instance;
    }

    /**
     * 重置容器（用于测试）
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}