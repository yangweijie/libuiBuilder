<?php

namespace Kingbes\Libui\Declarative\Components;

use FFI\CData;
// 增强的基础组件类
use Kingbes\Libui\Declarative\EventBus;
use Kingbes\Libui\Declarative\StateManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Throwable;

// 完整的基础组件类
abstract class Component
{
    protected array $attributes = [];
    protected array $children = [];
    protected ?CData $handle = null;
    protected ?string $ref = null;
    protected array $eventHandlers = [];
    protected Logger $logger;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->ref = $this->getAttribute('ref');

        // 初始化日志记录器
        $this->logger = new Logger('component');
        $streamHandler = new StreamHandler(__DIR__ . '/../../../../logs/error.log', Logger::ERROR);
        $formatter = new LineFormatter(null, null, true, true);
        $streamHandler->setFormatter($formatter);
        $this->logger->pushHandler($streamHandler);

        // 注册组件引用
        if ($this->ref) {
            StateManager::registerComponent($this->ref, $this);
        }

        // 解析事件绑定
        $this->parseEventBindings();
    }

    // 获取属性值 - 这个是关键方法
    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    // 设置属性值
    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    // 批量设置属性
    public function setAttributes(array $attributes): void
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    // 添加子组件
    public function addChild(Component $child): void
    {
        $this->children[] = $child;
    }

    // 获取所有子组件
    public function getChildren(): array
    {
        return $this->children;
    }

    // 查找子组件
    public function findChild(string $ref): ?Component
    {
        foreach ($this->children as $child) {
            if ($child->getAttribute('ref') === $ref) {
                return $child;
            }

            $found = $child->findChild($ref);
            if ($found) {
                return $found;
            }
        }
        return null;
    }

    // 获取组件句柄
    public function getHandle(): ?CData
    {
        return $this->handle;
    }

    // 获取组件引用名
    public function getRef(): ?string
    {
        return $this->ref;
    }

    // 解析事件绑定
    protected function parseEventBindings(): void
    {
        foreach ($this->attributes as $key => $value) {
            // @click="handleClick" 语法
            if (str_starts_with($key, '@')) {
                $event = substr($key, 1);
                $this->eventHandlers[$event] = $this->parseEventHandler($value);
            }
            // on-click="handleClick" 语法
            elseif (str_starts_with($key, 'on-')) {
                $event = str_replace('-', '_', substr($key, 3));
                $this->eventHandlers[$event] = $this->parseEventHandler($value);
            }
        }
    }

    // 解析事件处理器
    protected function parseEventHandler(string $handler): callable
    {
        // 现在只支持纯 PHP 语法
        return $this->parsePhpCall($handler);
    }

    // 解析 PHP 调用
    protected function parsePhpCall(string $call): callable
    {
        return function(...$args) use ($call) {
            try {
                // 定义一个函数确保所有必要的函数和变量在 eval 环境中可用
                $executor = function($call, $args, $thisRef) {
                    // 直接在 eval 环境中确保函数和变量可用
                    $code = "
                        // 检查函数是否已定义，如果没有则定义
                        if (!function_exists('setState')) {
                            function setState(\$key, \$value) {
                                return \\Kingbes\\Libui\\Declarative\\StateManager::set(\$key, \$value);
                            }
                        }
                        if (!function_exists('getState')) {
                            function getState(\$key, \$default = null) {
                                return \\Kingbes\\Libui\\Declarative\\StateManager::get(\$key, \$default);
                            }
                        }
                        if (!function_exists('watch')) {
                            function watch(\$key, \$callback) {
                                return \\Kingbes\\Libui\\Declarative\\StateManager::watch(\$key, \$callback);
                            }
                        }
                        if (!function_exists('emit')) {
                            function emit(\$event, ...\$args) {
                                return \\Kingbes\\Libui\\Declarative\\EventBus::emit(\$event, ...\$args);
                            }
                        }
                        if (!function_exists('getComponent')) {
                            function getComponent(\$ref) {
                                return \\Kingbes\\Libui\\Declarative\\StateManager::getComponent(\$ref);
                            }
                        }

                        // 定义变量
                        \$args = " . var_export($args, true) . ";
                        \$componentThis = \$thisRef;

                        // 根据调用类型执行
                        \$isStatement = str_starts_with(trim(" . var_export($call, true) . "), 'echo') || 
                                       str_starts_with(trim(" . var_export($call, true) . "), 'return') || 
                                       strpos(" . var_export($call, true) . ", ';') !== false;

                        if (\$isStatement) {
                            // 执行语句
                            return eval(" . var_export($call, true) . ");
                        } else {
                            // 执行表达式
                            return eval('return ' . var_export($call, true) . ';');
                        }
                    ";
                    
                    return eval($code);
                };

                return $executor($call, $args, $this);
            } catch (Throwable $e) {
                // 记录错误日志，包含组件信息
                $this->logger->error("PHP call error", [
                    'component' => $this->getTagName(),
                    'ref' => $this->ref,
                    'call' => $call,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                // 同时输出错误到控制台
                echo "Error in event handler: " . $e->getMessage() . "\n";
                return null;
            }
        };
    }

    // 解析静态方法调用
    protected function parseStaticMethodCall(string $call): callable
    {
        return function(...$args) use ($call) {
            [$class, $method] = explode('::', $call, 2);

            if (class_exists($class) && method_exists($class, $method)) {
                return $class::$method(...$args);
            }

            return null;
        };
    }

    // 解析箭头函数
    protected function parseArrowFunction(string $func): callable
    {
        return function(...$args) use ($func) {
            try {
                // 解析箭头函数语法
                if (preg_match('/^(.+?)\s*=>\s*(.+)$/', $func, $matches)) {
                    $params = $matches[1];
                    $body = $matches[2];

                    // 创建执行环境
                    $context = [
                        'this' => $this,
                        'args' => $args,
                        'setState' => fn($key, $value) => StateManager::set($key, $value),
                        'getState' => fn($key, $default = null) => StateManager::get($key, $default),
                        'emit' => fn($event, ...$args) => EventBus::emit($event, ...$args),
                    ];

                    extract($context);

                    return eval("return function({$params}) use (\$context) { extract(\$context); return {$body}; }(...\$args);");
                }
            } catch (Throwable $e) {
                $this->logger->error("Arrow function error", [
                    'component' => $this->getTagName(),
                    'ref' => $this->ref,
                    'function' => $func,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        };
    }

    // 获取其他组件的值
    public function getComponentValue(string $ref): null
    {
        $component = StateManager::getComponent($ref);
        return $component?->getValue();
    }

    // 设置其他组件的值
    protected function setComponentValue(string $ref, $value): void
    {
        $component = StateManager::getComponent($ref);
        $component?->setValue($value);
    }

    // 触发其他组件事件
    protected function triggerComponentEvent(string $ref, string $event, ...$args): void
    {
        $component = StateManager::getComponent($ref);
        if ($component && isset($component->eventHandlers[$event])) {
            $component->eventHandlers[$event](...$args);
        }
    }

    // 获取兄弟组件
    protected function getSibling(string $ref)
    {
        // 通过父组件查找兄弟组件
        // 这里需要在父组件中实现
        return StateManager::getComponent($ref);
    }

    // 获取父组件
    public function getParent(): ?Component
    {
        // 需要在渲染时维护父子关系
        return null;
    }

    // 执行事件处理器
    public function executeEventHandler(string $eventName, ...$args)
    {
        if (isset($this->eventHandlers[$eventName])) {
            $handler = $this->eventHandlers[$eventName];
            if (is_callable($handler)) {
                return $handler(...$args);
            }
        }
        return null;
    }

    // 组件销毁前的清理工作
    public function beforeDestroy(): void
    {
        // 触发销毁事件
        EventBus::emit('component:before_destroy', $this->ref, $this);

        // 清理事件监听
        foreach ($this->eventHandlers as $event => $handler) {
            EventBus::off($event, $handler);
        }
    }

    // 组件挂载后的初始化工作
    public function mounted(): void
    {
        // 触发挂载事件
        EventBus::emit('component:mounted', $this->ref, $this);

        // 执行挂载后的钩子
        if (isset($this->eventHandlers['mounted'])) {
            $this->eventHandlers['mounted']();
        }
    }

    // 组件更新
    public function updated(): void
    {
        EventBus::emit('component:updated', $this->ref, $this);

        if (isset($this->eventHandlers['updated'])) {
            $this->eventHandlers['updated']();
        }
    }

    // 抽象方法 - 子类必须实现
    abstract public function render(): CData;
    abstract public function getTagName(): string;
    abstract public function getValue();
    abstract public function setValue($value): void;

    // 魔术方法 - 方便调试
    public function __toString(): string
    {
        return $this->getTagName() . (empty($this->ref) ? '' : '#{$this->ref}');
    }

    // 导出组件状态
    public function exportState(): array
    {
        return [
            'tag' => $this->getTagName(),
            'ref' => $this->ref,
            'attributes' => $this->attributes,
            'value' => $this->getValue(),
            'children_count' => count($this->children),
            'events' => array_keys($this->eventHandlers),
        ];
    }

    // 调试函数 - 方便定位错误组件
    public function debugLog(string $message, array $context = []): void
    {
        $context['component'] = $this->getTagName();
        $context['ref'] = $this->ref;
        $this->logger->info($message, $context);
    }

    // 错误日志函数
    public function errorLog(string $message, array $context = []): void
    {
        $context['component'] = $this->getTagName();
        $context['ref'] = $this->ref;
        $this->logger->error($message, $context);
    }
}