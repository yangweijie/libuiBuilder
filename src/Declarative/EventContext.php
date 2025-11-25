<?php
namespace Kingbes\Libui\Declarative;

// 事件上下文管理器 - 为事件提供完整的执行环境
use Kingbes\Libui\Declarative\Components\Component;
use Kingbes\Libui\Window;
use Throwable;

class EventContext
{
    private Component $component;
    private array $extraData = [];

    public function __construct(Component $component, array $extraData = [])
    {
        $this->component = $component;
        $this->extraData = $extraData;
    }

    // 创建完整的执行环境
    public function createContext(): array
    {
        return array_merge([
            // 组件相关
            'component' => $this->component,
            'this' => $this->component,
            '$this' => $this->component,
            'ref' => $this->component->getRef(),
            'handle' => $this->component->getHandle(),

            // 状态管理 - 直接提供函数，不是类
            'getState' => fn($key, $default = null) => StateManager::get($key, $default),
            'setState' => fn($key, $value) => StateManager::set($key, $value),
            'watch' => fn($key, $callback) => StateManager::watch($key, $callback),
            'batch' => fn($callback) => StateManager::batch($callback),

            // 组件操作
            'getComponent' => fn($ref) => StateManager::getComponent($ref),
            'getValue' => fn($ref) => StateManager::getComponent($ref)?->getValue(),
            'setValue' => fn($ref, $value) => StateManager::getComponent($ref)?->setValue($value),
            'getComponentValue' => fn($ref) => $this->component->getComponentValue($ref),
            'setComponentValue' => fn($ref, $value) => $this->component->setComponentValue($ref, $value),

            // 事件系统
            'on' => fn($event, $callback) => EventBus::on($event, $callback),
            'off' => fn($event, $callback = null) => EventBus::off($event, $callback),
            'emit' => fn($event, ...$args) => EventBus::emit($event, ...$args),

            // 实用工具
            'console' => new \stdClass(), // 将在下面扩展
            'alert' => fn($message) => $this->showAlert($message),
            'confirm' => fn($message) => $this->showConfirm($message),

            // 额外数据
            'event' => $this->extraData['event'] ?? null,
            'args' => $this->extraData['args'] ?? [],
            'timestamp' => time(),

            // 常用工具函数
            'parseInt' => fn($value) => (int)$value,
            'parseFloat' => fn($value) => (float)$value,
            'strlen' => 'strlen',
            'trim' => 'trim',
            'substr' => 'substr',
            'json_encode' => 'json_encode',
            'json_decode' => 'json_decode',

        ], $this->extraData);
    }

    // 安全执行事件处理器（修正版）
    public function execute(callable $handler, array $args = []): mixed
    {
        $context = $this->createContext();

        // 扩展 console 对象 - 现在使用新的日志系统
        $context['console']->log = function(...$data) {
            $output = "CONSOLE LOG: " . implode(' ', array_map(fn($item) => is_string($item) ? $item : print_r($item, true), $data));
            echo $output . "\n";  // 输出到控制台
            
            // 使用新的日志系统
            LoggerManager::info($output, [
                'component' => $this->component->getTagName(),
                'ref' => $this->component->getRef()
            ]);
        };
        $context['console']->warn = function(...$data) {
            $output = "CONSOLE WARN: " . implode(' ', array_map(fn($item) => is_string($item) ? $item : print_r($item, true), $data));
            echo $output . "\n";  // 输出到控制台
            
            // 使用新的日志系统
            LoggerManager::warning($output, [
                'component' => $this->component->getTagName(),
                'ref' => $this->component->getRef()
            ]);
        };
        $context['console']->error = function(...$data) {
            $output = "CONSOLE ERROR: " . implode(' ', array_map(fn($item) => is_string($item) ? $item : print_r($item, true), $data));
            echo $output . "\n";  // 输出到控制台
            
            // 使用新的日志系统
            LoggerManager::error($output, [
                'component' => $this->component->getTagName(),
                'ref' => $this->component->getRef()
            ]);
        };

        try {
            // 创建闭包执行器
            $executor = function() use ($handler, $args, $context) {
                // 导入上下文变量到局部作用域
                extract($context);

                // 执行处理器
                return $handler(...$args);
            };

            return $executor();
        } catch (Throwable $e) {
            $this->handleError($e, $context);
            return null;
        }
    }

    // 错误处理 - 现在使用新的日志系统
    private function handleError(Throwable $e, array $context): void
    {
        $errorInfo = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'component' => $this->component->getTagName(),
            'ref' => $this->component->getRef(),
            'available_functions' => array_filter(array_keys($context), fn($key) => !is_object($context[$key])),
        ];

        // 使用新的日志系统记录错误
        LoggerManager::componentError(
            $this->component->getTagName(),
            $this->component->getRef(),
            "Event execution error: " . $e->getMessage(),
            $errorInfo
        );
        
        EventBus::emit('component:error', $this->component->getRef(), $errorInfo);
    }

    // 显示警告框
    private function showAlert(string $message): void
    {
        // 如果有窗口引用，使用libui的msgBox
        $window = StateManager::getComponent('mainWindow');
        if ($window && $window->getHandle()) {
            Window::msgBox($window->getHandle(), "提示", $message);
        } else {
            LoggerManager::info("ALERT: " . $message, [
                'component' => $this->component->getTagName(),
                'ref' => $this->component->getRef()
            ]);
        }
    }

    // 显示确认框
    private function showConfirm(string $message): bool
    {
        $window = StateManager::getComponent('mainWindow');
        if ($window && $window->getHandle()) {
            return Window::msgBox($window->getHandle(), "确认", $message) === 1;
        }

        LoggerManager::info("CONFIRM: " . $message, [
            'component' => $this->component->getTagName(),
            'ref' => $this->component->getRef()
        ]);
        return false;
    }
}