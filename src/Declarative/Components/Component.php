<?php

namespace Kingbes\Libui\Declarative\Components;

use FFI\CData;
// 增强的基础组件类
use Kingbes\Libui\Control;
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
    protected array $dynamicAttributes = []; // 动态属性跟踪
    private static ?array $registeredFunctions = null; // 注册的全局函数（使用 StateManager 存储以保证全局）

    public function __construct(array $attributes = [])
    {
        // 初始化日志记录器
        $this->logger = new Logger('component');
        $streamHandler = new StreamHandler(__DIR__ . '/../../../../logs/error.log', Logger::ERROR);
        $formatter = new LineFormatter(null, null, true, true);
        $streamHandler->setFormatter($formatter);
        $this->logger->pushHandler($streamHandler);

        // 过滤属性，只保留支持的属性
        $filteredAttributes = $this->filterAttributes($attributes);
        
        $this->attributes = $filteredAttributes;
        $this->ref = $this->getAttribute('ref');

        // 注册组件引用
        if ($this->ref) {
            StateManager::registerComponent($this->ref, $this);
        }

        // 解析特殊属性
        $this->parseSpecialAttributes();
        $this->parseEventBindings();
        $this->parseDynamicAttributes();
    }
    
    // 过滤属性，只保留支持的属性
    protected function filterAttributes(array $attributes): array
    {
        $supportedAttributes = $this->getSupportedAttributes();
        $filtered = [];
        
        foreach ($attributes as $key => $value) {
            // 保留特殊属性（v-model, ref, v-show, @事件, :动态属性）
            if (str_starts_with($key, 'v-') || 
                str_starts_with($key, '@') || 
                str_starts_with($key, ':') || 
                $key === 'ref' || 
                $key === 'style' || 
                in_array($key, $supportedAttributes)) {
                $filtered[$key] = $value;
            } else {
                // 记录不支持的属性
                $this->logger->debug("Ignoring unsupported attribute", [
                    'component' => $this->getTagName(),
                    'ref' => $this->ref,
                    'attribute' => $key
                ]);
            }
        }
        
        return $filtered;
    }
    
    // 获取组件支持的属性列表
    protected function getSupportedAttributes(): array
    {
        // 由子类实现，返回该组件支持的属性列表
        // 基础组件支持通用属性
        return ['ref', 'class', 'id', 'style'];
    }
    
    // 获取所有属性（包括动态属性）
    public function getAllAttributes(): array
    {
        return $this->attributes;
    }
    
    // 注册全局函数到沙箱
    public static function registerGlobalFunction(string $name, callable $handler): void
    {
        // 使用 StateManager 作为全局存储
        $functions = self::getGlobalRegisteredFunctions();
        $functions[$name] = $handler;
        \Kingbes\Libui\Declarative\StateManager::set('_global_registered_functions', $functions);
    }
    
    // 获取全局注册的函数
    private static function getGlobalRegisteredFunctions(): array
    {
        $functions = \Kingbes\Libui\Declarative\StateManager::get('_global_registered_functions', []);
        return is_array($functions) ? $functions : [];
    }
    
    // 获取注册的全局函数
    protected function getRegisteredFunctions(): array
    {
        if (self::$registeredFunctions === null) {
            self::$registeredFunctions = self::getGlobalRegisteredFunctions();
        }
        return self::$registeredFunctions ?? [];
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

    // 解析特殊属性
    protected function parseSpecialAttributes(): void
    {
        // 解析 v-model - 由子类处理
        if (isset($this->attributes['v-model'])) {
            $this->handleVModel($this->attributes['v-model']);
        }

        // 解析 ref - 已在构造函数中处理
        // 解析 v-show - 处理条件显示
        if (isset($this->attributes['v-show'])) {
            $this->handleVShow($this->attributes['v-show']);
        }
    }

    // 处理 v-model 绑定
    protected function handleVModel(string $modelPath): void
    {
        // 由子类实现特定的 v-model 处理逻辑
        // 子类需要监听状态变化并更新组件，同时在组件值变化时更新状态
    }

    // 处理 v-show 条件显示
    protected function handleVShow(string $condition): void
    {
        // 保存 v-show 条件，以便在组件挂载后处理
        $this->attributes['v-show-condition'] = $condition;
    }
    
    // 处理 v-show 的可见性
    protected function applyVShowVisibility(): void
    {
        $condition = $this->getAttribute('v-show-condition');
        if (!$condition) {
            return;
        }

        // 处理简单的 true/false 值
        if ($condition === 'true') {
            $shouldShow = true;
        } elseif ($condition === 'false') {
            $shouldShow = false;
        } else {
            // 检查是否包含 PHP 语法（getState、函数调用等）
            if ($this->containsExpression($condition)) {
                // 包含 PHP 语法，使用沙箱评估表达式
                $shouldShow = $this->evaluateExpression($condition);
                
                // 提取 getState 参数并监听状态变化
                $this->setupVShowWatcher($condition);
            } else {
                // 不包含 PHP 语法，作为状态管理器键处理
                $shouldShow = StateManager::get($condition, true); // 默认为 true
                
                // 监听状态变化
                StateManager::watch($condition, function($newValue) {
                    $isVisible = (bool)$newValue;
                    if ($this->handle) {
                        if ($isVisible) {
                            Control::show($this->handle);
                        } else {
                            Control::hide($this->handle);
                        }
                    }
                });
            }
        }

        // 设置初始可见性
        if ($this->handle) {
            if ($shouldShow) {
                Control::show($this->handle);
            } else {
                Control::hide($this->handle);
            }
        }
    }

    // 设置 v-show 状态监听器
    private function setupVShowWatcher(string $condition): void
    {
        // 提取表达式中的状态键
        $pattern = '/getState\\(\'([^\']+)\'(?:,\\s*\'([^\']*)\')?\)/';
        $matches = [];
        preg_match_all($pattern, $condition, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $key) {
                // 监听状态变化并更新可见性
                StateManager::watch($key, function($newValue) use ($condition) {
                    $this->updateVShowVisibility($condition);
                });
            }
        }

        // 如果是嵌套属性（如 form.city），还需要监听父对象的变化
        $pattern = '/getState\\(\'([a-z0-9_]+\\.[a-z0-9_]+)\'/i';
        $matches = [];
        preg_match_all($pattern, $condition, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $fullKey) {
                $parts = explode('.', $fullKey);
                $parentKey = implode('.', array_slice($parts, 0, -1)); // 获取父键，如 'form'

                // 监听父对象的变化
                StateManager::watch($parentKey, function($newValue) use ($condition) {
                    $this->updateVShowVisibility($condition);
                });
            }
        }
    }

    // 更新 v-show 可见性
    private function updateVShowVisibility(string $condition): void
    {
        if (!$this->handle) {
            return; // 只有在组件被渲染后才能更新可见性
        }

        $result = $this->evaluateExpression($condition);
        $isVisible = (bool)$result;
        
        if ($isVisible) {
            Control::show($this->handle);
        } else {
            Control::hide($this->handle);
        }
    }

    // 解析动态属性
    protected function parseDynamicAttributes(): void
    {
        foreach ($this->attributes as $key => $value) {
            if (str_starts_with($key, ':')) {
                // 动态属性，如 :text="getState('form.title', 'Default')"
                $attributeName = substr($key, 1); // 移除冒号前缀
                $this->dynamicAttributes[$attributeName] = $value;
                
                // 如果值包含表达式（有 getState 等函数调用），则需要监听状态变化
                if ($this->containsExpression($value)) {
                    $this->setupDynamicAttributeUpdate($attributeName, $value);
                } else {
                    // 静态字符串，直接设置属性
                    $this->setAttribute($attributeName, $value);
                }
            }
        }
    }

    // 检查字符串是否包含表达式
    protected function containsExpression(string $value): bool
    {
        // 如果包含单引号，很可能是包含函数调用的表达式
        return strpos($value, "getState") !== false || 
               strpos($value, "setState") !== false || 
               strpos($value, " . ") !== false || 
               strpos($value, ' . ') !== false;
    }

    // 设置动态属性更新
    protected function setupDynamicAttributeUpdate(string $attributeName, string $expression): void
    {
        // 提取表达式中的状态键
        $pattern = '/getState\(\'([^\']+)\'(?:,\s*\'([^\']*)\')?\)/';
        $matches = [];
        preg_match_all($pattern, $expression, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $key) {
                // 监听状态变化并更新属性
                StateManager::watch($key, function($newValue) use ($attributeName, $expression) {
                    $this->updateDynamicAttribute($attributeName, $expression);
                });
            }
        }

        // 如果是嵌套属性（如 form.city），还需要监听父对象的变化
        $pattern = '/getState\(\'([a-z0-9_]+\.[a-z0-9_]+)\'/i';
        $matches = [];
        preg_match_all($pattern, $expression, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $fullKey) {
                $parts = explode('.', $fullKey);
                $parentKey = implode('.', array_slice($parts, 0, -1)); // 获取父键，如 'form'

                // 监听父对象的变化
                StateManager::watch($parentKey, function($newValue) use ($attributeName, $expression) {
                    $this->updateDynamicAttribute($attributeName, $expression);
                });
            }
        }
    }

    // 更新动态属性
    protected function updateDynamicAttribute(string $attributeName, string $expression): void
    {
        if (!$this->handle) {
            return; // 只有在组件被渲染后才能更新属性
        }

        $newValue = $this->evaluateExpression($expression);
        
        // 检查值是否真的发生了变化，避免不必要的更新
        $currentValue = $this->getAttribute($attributeName);
        if ($currentValue === $newValue) {
            return; // 值没有变化，不需要更新
        }

        $this->setAttribute($attributeName, $newValue);
        
        // 根据属性名调用适当的 setter 方法
        $this->applyAttribute($attributeName, $newValue);
    }

    // 应用属性到组件
    protected function applyAttribute(string $attributeName, $value): void
    {
        // 由子类实现具体属性应用逻辑
        // 例如，对于 label 组件的 text 属性，调用 setText 方法
    }

    // 设置样式（用于 v-show 等）
    protected function setStyle(string $property, string $value): void
    {
        $currentStyle = $this->getAttribute('style', '');
        $styleParts = [];
        
        // 解析现有的样式
        $styles = explode(';', $currentStyle);
        foreach ($styles as $style) {
            $style = trim($style);
            if ($style) {
                $parts = explode(':', $style, 2);
                if (count($parts) === 2) {
                    $prop = trim($parts[0]);
                    if ($prop !== $property) {
                        $styleParts[] = $style;
                    }
                }
            }
        }
        
        // 添加新样式
        if ($value !== '') {
            $styleParts[] = "{$property}: {$value}";
        }
        
        $newStyle = implode('; ', $styleParts);
        $this->setAttribute('style', $newStyle);
    }

    // 评估条件
    protected function evaluateCondition(string $condition): bool
    {
        // 使用和 TemplateParser 相同的逻辑评估条件
        if (strpos($condition, 'getState') !== false) {
            // 处理 getState 调用
            $pattern = '/getState\(\'([^\']+)\'(?:,\s*\'([^\']*)\')?\)/';
            $evaluated = preg_replace_callback($pattern, function ($matches) {
                $key = $matches[1];
                $default = $matches[2] ?? '';
                $value = \Kingbes\Libui\Declarative\StateManager::get($key, $default);
                return is_string($value) ? "'{$value}'" : ($value ? 'true' : 'false');
            }, $condition);

            // 简单评估布尔表达式
            try {
                $result = eval("return {$evaluated};");
                return (bool)$result;
            } catch (Throwable $e) {
                $this->logger->error("Condition evaluation error", [
                    'component' => $this->getTagName(),
                    'ref' => $this->ref,
                    'condition' => $condition,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
        }

        // 直接评估简单的值
        return (bool)trim($condition, "'\"");
    }

    // 监听条件中使用的状态
    protected function watchCondition(string $condition, callable $callback): void
    {
        $pattern = '/getState\(\'([^\']+)\'(?:,\s*\'([^\']*)\')?\)/';
        $matches = [];
        preg_match_all($pattern, $condition, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $key) {
                StateManager::watch($key, $callback);
            }
        }
    }

    // 评估表达式
    protected function evaluateExpression(string $expression)
    {
        // 处理 getState 调用，但保留原始表达式的结构
        $pattern = '/getState\(\'([^\']+)\'(?:,\s*\'([^\']*)\')?\)/';
        
        // 先提取所有的 getState 调用及其参数
        preg_match_all($pattern, $expression, $matches, PREG_SET_ORDER);
        
        $evaluatedExpression = $expression;
        foreach ($matches as $match) {
            $fullMatch = $match[0];
            $key = $match[1];
            $default = $match[2] ?? '';
            $value = \Kingbes\Libui\Declarative\StateManager::get($key, $default);
            
            // 根据值的类型进行不同的处理
            if (is_string($value)) {
                // 对于字符串值，用引号包围
                $replacement = "'" . addslashes($value) . "'";
            } else {
                // 对于非字符串值，直接转换为字符串
                $replacement = var_export($value, true);
            }
            
            $evaluatedExpression = str_replace($fullMatch, $replacement, $evaluatedExpression);
        }

        // 评估表达式
        try {
            return eval("return {$evaluatedExpression};");
        } catch (Throwable $e) {
            $this->logger->error("Expression evaluation error", [
                'component' => $this->getTagName(),
                'ref' => $this->ref,
                'expression' => $expression,
                'error' => $e->getMessage()
            ]);
            return null;
        }
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
                // 使用 PHPSandbox 来安全执行 PHP 代码
                $sandbox = new \PHPSandbox\PHPSandbox();
                
                // 为沙箱添加必要的函数
                $sandbox->defineFunc('setState', function($key, $value) {
                    return \Kingbes\Libui\Declarative\StateManager::set($key, $value);
                });
                $sandbox->defineFunc('getState', function($key, $default = null) {
                    return \Kingbes\Libui\Declarative\StateManager::get($key, $default);
                });
                $sandbox->defineFunc('watch', function($key, $callback) {
                    return \Kingbes\Libui\Declarative\StateManager::watch($key, $callback);
                });
                $sandbox->defineFunc('emit', function($event, ...$callbackArgs) {
                    return \Kingbes\Libui\Declarative\EventBus::emit($event, ...$callbackArgs);
                });
                $sandbox->defineFunc('getComponent', function($ref) {
                    return \Kingbes\Libui\Declarative\StateManager::getComponent($ref);
                });
                $sandbox->defineFunc('strlen', 'strlen');
                $sandbox->defineFunc('count', 'count');
                $sandbox->defineFunc('array_keys', 'array_keys');
                $sandbox->defineFunc('implode', 'implode');
                $sandbox->defineFunc('explode', 'explode');
                $sandbox->defineFunc('trim', 'trim');
                $sandbox->defineFunc('substr', 'substr');
                $sandbox->defineFunc('strtolower', 'strtolower');
                $sandbox->defineFunc('strtoupper', 'strtoupper');
                $sandbox->defineFunc('ucfirst', 'ucfirst');
                $sandbox->defineFunc('lcfirst', 'lcfirst');
                $sandbox->defineFunc('ucwords', 'ucwords');
                $sandbox->defineFunc('json_encode', 'json_encode');
                $sandbox->defineFunc('json_decode', 'json_decode');
                $sandbox->defineFunc('print_r', 'print_r');
                $sandbox->defineFunc('var_dump', 'var_dump');
                
                // 添加注册的全局函数
                $registeredFuncs = $this->getRegisteredFunctions();
                foreach ($registeredFuncs as $name => $handler) {
                    $sandbox->defineFunc($name, $handler);
                }
                
                // 调试：输出注册的函数
                // echo "Registered functions: " . implode(', ', array_keys($registeredFuncs)) . "\n";
            // 调试：输出注册的函数
                // echo "Registered functions: " . implode(', ', array_keys($registeredFuncs)) . "\n";
                
                // 捕获沙箱执行的输出
                $output = '';
                $result = null;
                
                // 临时重定向输出以捕获 echo/print 等函数的输出
                ob_start();
                try {
                    // 如果 $call 看起来像一个简单的函数调用（没有分号），添加分号使其成为完整语句
                    $codeToExecute = $call;
                    if (!str_contains($call, ';') && !str_starts_with(trim($call), '<?php')) {
                        // 如果是函数调用但没有分号，添加分号
                        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*\s*\(/', trim($call))) {
                            $codeToExecute = $call . ';';
                        }
                    }
                    
                    $result = $sandbox->execute($codeToExecute);
                    $output = ob_get_contents();
                } catch (\Throwable $e) {
                    // 如果执行失败，捕获输出
                    if (ob_get_level() > 0) {
                        $output = ob_get_contents();
                        ob_end_clean();
                    }
                    
                    // 记录错误日志，包含组件信息
                    $this->logger->error("PHP call error", [
                        'component' => $this->getTagName(),
                        'ref' => $this->ref,
                        'call' => $call,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                    
                    // 提供更友好的错误信息
                    $error_msg = "Error in event handler: " . $e->getMessage();
                    
                    // 如果是解析错误，提供具体建议
                    if (strpos($e->getMessage(), 'Could not parse') !== false) {
                        $error_msg .= "\nHint: Check if you're calling undefined functions in your event handler. ";
                        $error_msg .= "Only predefined functions are allowed in event handlers.";
                        
                        // 尝试提供更具体的建议
                        $error_msg .= "\nYou may need to register custom functions using Component::registerGlobalFunction().";
                    }
                    
                    echo $error_msg . "\n";
                    return null;
                } finally {
                    if (ob_get_level() > 0) { // 确保缓冲区存在
                        $current_output = ob_get_contents();
                        if ($current_output !== false) {
                            $output = $current_output;
                        }
                        ob_end_clean(); // 清除缓冲区而不输出
                    }
                }
                
                // 如果有输出，将其输出到终端
                if ($output !== '') {
                    echo $output;
                }
                
                return $result;
            } catch (\Throwable $e) {
                // 记录错误日志，包含组件信息
                $this->logger->error("PHP call error", [
                    'component' => $this->getTagName(),
                    'ref' => $this->ref,
                    'call' => $call,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                
                // 提供更友好的错误信息
                $error_msg = "Error in event handler: " . $e->getMessage();
                
                // 如果是解析错误，提供具体建议
                if (strpos($e->getMessage(), 'Could not parse') !== false) {
                    $error_msg .= "\n sandbox {$call}";
                    $error_msg .= "\nHint: Check if you're calling undefined functions in your event handler. ";
                    $error_msg .= "Only predefined functions are allowed in event handlers.";
                    
                    // 尝试提供更具体的建议
                    $error_msg .= "\nYou may need to register custom functions using Component::registerGlobalFunction().";
                }
                
                echo $error_msg . "\n";
                error_log($e->getMessage().PHP_EOL.$e->getTraceAsString());
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

        // 应用 v-show 可见性设置
        $this->applyVShowVisibility();
        
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