<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Core\Config;

use League\Config\Configuration;
use League\Config\ConfigurationInterface;
use Nette\Schema\Expect;

/**
 * 配置管理器
 * 封装 league/config 提供类型安全的配置管理
 */
class ConfigManager
{
    private ConfigurationInterface $config;

    public function __construct(array $initialConfig = [])
    {
        $schema = [
            'app' => Expect::structure([
                'title' => Expect::string()->default('libui Application'),
                'width' => Expect::int()->default(640),
                'height' => Expect::int()->default(480),
                'margined' => Expect::bool()->default(true),
            ]),
            'builder' => Expect::structure([
                'auto_register' => Expect::bool()->default(true),
                'enable_logging' => Expect::bool()->default(false),
                'default_state_manager' => Expect::string()->default('default'),
            ]),
            'events' => Expect::structure([
                'enabled' => Expect::bool()->default(true),
                'namespace' => Expect::string()->default('builder'),
                'global_listeners' => Expect::array()->default([]),
            ]),
            'logging' => Expect::structure([
                'level' => Expect::string()->default('info')->transform(function($value) {
                    $allowed = ['debug', 'info', 'warning', 'error'];
                    if (!in_array($value, $allowed, true)) {
                        return 'info'; // 回退到默认值
                    }
                    return $value;
                }),
                'path' => Expect::string()->default('logs/builder.log'),
            ]),
            'dependencies' => Expect::array()->default([]),
        ];

        $this->config = new Configuration($schema);
        
        // 设置默认配置
        $this->merge($initialConfig);
    }

    /**
     * 获取配置值
     *
     * @param string|null $key 配置键，支持点语法如 'app.title'
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->config->toArray();
        }

        return $this->config->get($key, $default);
    }

    /**
     * 设置配置值
     *
     * @param string $key 配置键
     * @param mixed $value 配置值
     */
    public function set(string $key, $value): void
    {
        $this->config->set($key, $value);
    }

    /**
     * 合并配置
     *
     * @param array $config 配置数组
     */
    public function merge(array $config): void
    {
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $existing = $this->config->get($key, []);
                $this->config->set($key, array_merge($existing, $value));
            } else {
                $this->config->set($key, $value);
            }
        }
    }

    /**
     * 验证配置
     *
     * @return bool
     * @throws \Nette\Schema\ValidationException
     */
    public function validate(): bool
    {
        $this->config->validate();
        return true;
    }

    /**
     * 获取配置实例
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration(): ConfigurationInterface
    {
        return $this->config;
    }

    /**
     * 从文件加载配置
     *
     * @param string $filePath 配置文件路径
     * @return $this
     */
    public function loadFromFile(string $filePath): self
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Config file not found: $filePath");
        }

        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $config = [];

        switch ($ext) {
            case 'php':
                $config = require $filePath;
                break;
            case 'json':
                $config = json_decode(file_get_contents($filePath), true);
                break;
            case 'yaml':
            case 'yml':
                if (!function_exists('yaml_parse_file')) {
                    throw new \RuntimeException('YAML extension required');
                }
                $config = yaml_parse_file($filePath);
                break;
            default:
                throw new \RuntimeException("Unsupported config format: $ext");
        }

        $this->merge($config);
        return $this;
    }

    /**
     * 保存配置到文件
     *
     * @param string $filePath 目标文件路径
     * @param string $format 格式 (php, json, yaml)
     */
    public function saveToFile(string $filePath, string $format = 'php'): void
    {
        $content = '';
        $config = $this->config->toArray();

        switch ($format) {
            case 'php':
                $content = '<?php' . PHP_EOL . 'return ' . var_export($config, true) . ';' . PHP_EOL;
                break;
            case 'json':
                $content = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                break;
            case 'yaml':
            case 'yml':
                if (!function_exists('yaml_emit')) {
                    throw new \RuntimeException('YAML extension required');
                }
                $content = yaml_emit($config);
                break;
            default:
                throw new \RuntimeException("Unsupported format: $format");
        }

        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($filePath, $content);
    }
}