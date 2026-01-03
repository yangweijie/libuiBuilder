<?php

namespace Kingbes\Libui\View;

use Kingbes\Libui\View\Builder\TabBuilder;
use Kingbes\Libui\View\Components\BoxBuilder;
use Kingbes\Libui\View\Components\ButtonBuilder;
use Kingbes\Libui\View\Components\CanvasBuilder;
use Kingbes\Libui\View\Components\CheckboxBuilder;
use Kingbes\Libui\View\Components\ComboboxBuilder;
use Kingbes\Libui\View\Components\DataGridBuilder;
use Kingbes\Libui\View\Components\EntryBuilder;
use Kingbes\Libui\View\Components\GridBuilder;
use Kingbes\Libui\View\Components\GroupBuilder;
use Kingbes\Libui\View\Components\LabelBuilder;
use Kingbes\Libui\View\Components\MenuBuilder;
use Kingbes\Libui\View\Components\MultilineEntryBuilder;
use Kingbes\Libui\View\Components\ProgressBarBuilder;
use Kingbes\Libui\View\Components\RadioBuilder;
use Kingbes\Libui\View\Components\SeparatorBuilder;
use Kingbes\Libui\View\Components\SliderBuilder;
use Kingbes\Libui\View\Components\SpinboxBuilder;
use Kingbes\Libui\View\Components\TableBuilder;
use Kingbes\Libui\View\Components\WindowBuilder;
use Kingbes\Libui\View\Validation\ComponentBuilder;
use RuntimeException;
use Throwable;

/**
 * 视图构建器 - 所有组件的入口
 */
class Builder
{
    private ComponentBuilder $currentComponent;
    private static array $extensions = [];

    /**
     * 创建新的 Builder 实例用于链式调用
     */
    public static function create(): static
    {
        return new static();
    }

    /**
 * 设置当前组件(私有辅助方法)
     */
    private function setCurrent(ComponentBuilder $component): static
    {
        $this->currentComponent = $component;
        return $this;
    }

    /**
     * 获取最终构建的组件
     */
    public function get(): ComponentBuilder
    {
        return $this->currentComponent;
    }
    // 窗口组件
    public static function window(array $config = []): WindowBuilder
    {
        return new WindowBuilder($config);
    }

    // 容器组件
    public static function vbox(array $config = []): BoxBuilder
    {
        return new BoxBuilder('vertical', $config);
    }

    public static function hbox(array $config = []): BoxBuilder
    {
        return new BoxBuilder('horizontal', $config);
    }

    public static function grid(array $config = []): GridBuilder
    {
        return new GridBuilder($config);
    }

    public static function tab(array $config = []): TabBuilder
    {
        return new TabBuilder($config);
    }

    // 控件组件
    public static function button(array $config = []): ButtonBuilder
    {
        return new ButtonBuilder($config);
    }

    public static function label(array $config = []): LabelBuilder
    {
        return new LabelBuilder($config);
    }

    public static function entry(array $config = []): EntryBuilder
    {
        return new EntryBuilder($config);
    }

    public static function checkbox(array $config = []): CheckboxBuilder
    {
        return new CheckboxBuilder($config);
    }

    public static function combobox(array $config = []): ComboboxBuilder
    {
        return new ComboboxBuilder($config);
    }

// 便捷方法
    public static function passwordEntry(array $config = []): EntryBuilder
    {
        return new EntryBuilder(array_merge($config, ['password' => true]));
    }

    public static function editableCombobox(array $config = []): ComboboxBuilder
    {
        return new ComboboxBuilder(array_merge($config, ['editable' => true]));
    }

    public static function table(array $config = []): TableBuilder
    {
        return new TableBuilder($config);
    }

    public static function dataGrid(array $config = []): DataGridBuilder
    {
        return new DataGridBuilder($config);
    }

    public static function menu(): MenuBuilder
    {
        return new MenuBuilder();
    }

    public static function canvas(array $config = []): CanvasBuilder
    {
        return new CanvasBuilder($config);
    }

    public static function separator(): SeparatorBuilder
    {
        return new SeparatorBuilder();
    }

    // 便捷方法
    public static function hSeparator(array $config = []): SeparatorBuilder
    {
        return new SeparatorBuilder(array_merge($config, ['orientation' => 'horizontal']));
    }

    public static function vSeparator(array $config = []): SeparatorBuilder
    {
        return new SeparatorBuilder(array_merge($config, ['orientation' => 'vertical']));
    }

    public static function multilineEntry(array $config = []): MultilineEntryBuilder
    {
        return new MultilineEntryBuilder($config);
    }

    public static function textarea(array $config = []): MultilineEntryBuilder
    {
        return new MultilineEntryBuilder($config);
    }

    public static function spinbox(array $config = []): SpinboxBuilder
    {
        return new SpinboxBuilder($config);
    }

    public static function slider(array $config = []): SliderBuilder
    {
        return new SliderBuilder($config);
    }

    public static function progressBar(array $config = []): ProgressBarBuilder
    {
        return new ProgressBarBuilder($config);
    }

    public static function radio(array $config = []): RadioBuilder
    {
        return new RadioBuilder($config);
    }

    public static function group(array $config = []): GroupBuilder
    {
        return new GroupBuilder($config);
    }

    // ========== 链式调用辅助函数 ==========
    
    /**
     * 配置当前组件的通用属性
     */
    public function config(string $key, $value): static
    {
        if (isset($this->currentComponent)) {
            $this->currentComponent->setConfig($key, $value);
        }
        return $this;
    }

    /**
     * 为当前组件设置ID
     */
    public function withId(string $id): static
    {
        if (isset($this->currentComponent)) {
            $this->currentComponent->id($id);
        }
        return $this;
    }

    /**
     * 为当前组件绑定状态
     */
    public function bindTo(string $stateKey): static
    {
        if (isset($this->currentComponent)) {
            $this->currentComponent->bind($stateKey);
        }
        return $this;
    }

    /**
     * 为当前组件添加事件处理器
     */
    public function addEvent(string $event, callable $handler): static
    {
        if (isset($this->currentComponent)) {
            $this->currentComponent->on($event, $handler);
        }
        return $this;
    }

    /**
     * 添加子组件到当前容器
     */
    public function child(ComponentBuilder $child): static
    {
        if (isset($this->currentComponent)) {
            $this->currentComponent->addChild($child);
        }
        return $this;
    }

    /**
     * 批量添加子组件到当前容器
     */
    public function children(array $children): static
    {
        if (isset($this->currentComponent)) {
            $this->currentComponent->contains($children);
        }
        return $this;
    }

    // ========== 快速创建组件并设为当前 ==========
    
    /**
     * 创建窗口并设为当前组件
     */
    public function newWindow(array $config = []): static
    {
        return $this->setCurrent(new WindowBuilder($config));
    }

    /**
     * 创建垂直容器并设为当前组件
     */
    public function newVbox(array $config = []): static
    {
        return $this->setCurrent(new BoxBuilder('vertical', $config));
    }

    /**
     * 创建水平容器并设为当前组件
     */
    public function newHbox(array $config = []): static
    {
        return $this->setCurrent(new BoxBuilder('horizontal', $config));
    }

    /**
     * 创建网格并设为当前组件
     */
    public function newGrid(array $config = []): static
    {
        return $this->setCurrent(new GridBuilder($config));
    }

    /**
     * 创建按钮并设为当前组件
     */
    public function newButton(array $config = []): static
    {
        return $this->setCurrent(new ButtonBuilder($config));
    }

    /**
     * 创建标签并设为当前组件
     */
    public function newLabel(array $config = []): static
    {
        return $this->setCurrent(new LabelBuilder($config));
    }

    /**
     * 创建输入框并设为当前组件
     */
    public function newEntry(array $config = []): static
    {
        return $this->setCurrent(new EntryBuilder($config));
    }

    /**
     * 创建复选框并设为当前组件
     */
    public function newCheckbox(array $config = []): static
    {
        return $this->setCurrent(new CheckboxBuilder($config));
    }

    /**
     * 创建下拉框并设为当前组件
     */
    public function newCombobox(array $config = []): static
    {
        return $this->setCurrent(new ComboboxBuilder($config));

    }
    /**
     * 创建多行输入框并设为当前组件
     */
    public function newTextarea(array $config = []): static
    {
        return $this->setCurrent(new MultilineEntryBuilder($config));
    }

    /**
     * 创建滑块并设为当前组件
     */
    public function newSlider(array $config = []): static
    {
        return $this->setCurrent(new SliderBuilder($config));
    }

    /**
     * 创建进度条并设为当前组件
     */
    public function newProgressBar(array $config = []): static
    {
        return $this->setCurrent(new ProgressBarBuilder($config));
    }

    /**
     * 创建标签页并设为当前组件
     */
    public function newTab(array $config = []): static
    {
        return $this->setCurrent(new TabBuilder($config));
    }

    /**
     * 创建表格并设为当前组件
     */
    public function newTable(array $config = []): static
    {
        return $this->setCurrent(new TableBuilder($config));
    }

    /**
     * 创建数据网格并设为当前组件
     */
    public function newDataGrid(array $config = []): static
    {
        return $this->setCurrent(new DataGridBuilder($config));
    }

    /**
     * 创建画布并设为当前组件
     */
    public function newCanvas(array $config = []): static
    {
        return $this->setCurrent(new CanvasBuilder($config));
    }

    /**
     * 创建分隔符并设为当前组件
     */
    public function newSeparator(array $config = []): static
    {
        return $this->setCurrent(new SeparatorBuilder($config));
    }

    /**
     * 创建水平分隔符并设为当前组件
     */
    public function newHSeparator(array $config = []): static
    {
        return $this->setCurrent(new SeparatorBuilder(array_merge($config, ['orientation' => 'horizontal'])));
    }

    /**
     * 创建垂直分隔符并设为当前组件
     */
    public function newVSeparator(array $config = []): static
    {
        return $this->setCurrent(new SeparatorBuilder(array_merge($config, ['orientation' => 'vertical'])));
    }

    /**
     * 创建数字输入框并设为当前组件
     */
    public function newSpinbox(array $config = []): static
    {
        return $this->setCurrent(new SpinboxBuilder($config));
    }

    /**
     * 创建单选按钮组并设为当前组件
     */
    public function newRadio(array $config = []): static
    {
        return $this->setCurrent(new RadioBuilder($config));
    }

    /**
     * 创建密码输入框并设为当前组件
     */
    public function newPasswordEntry(array $config = []): static
    {
        return $this->setCurrent(new EntryBuilder(array_merge($config, ['password' => true])));
    }

    /**
     * 创建可编辑下拉框并设为当前组件
     */
    public function newEditableCombobox(array $config = []): static
    {
        return $this->setCurrent(new ComboboxBuilder(array_merge($config, ['editable' => true])));
    }

    /**
     * 创建分组控件并设为当前组件
     */
    public function newGroup(array $config = []): static
    {
        return $this->setCurrent(new GroupBuilder($config));
    }

    // ========== 屏幕信息 ==========

    /**
     * 获取屏幕宽度
     */
    public static function screenWidth(): int
    {
        return self::screenInfo()['width'];
    }

    /**
     * 获取屏幕高度
     */
    public static function screenHeight(): int
    {
        return self::screenInfo()['height'];
    }

    /**
     * 获取屏幕信息
     */
    public static function screenInfo(): array
    {
        $default = ['width' => 1920, 'height' => 1080];

        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $output = shell_exec('wmic path Win32_VideoController get CurrentHorizontalResolution,CurrentVerticalResolution /format:value');
                if (preg_match('/CurrentHorizontalResolution=(\d+)/', $output, $matches)) {
                    $default['width'] = (int)$matches[1];
                }
                if (preg_match('/CurrentVerticalResolution=(\d+)/', $output, $matches)) {
                    $default['height'] = (int)$matches[1];
                }
            } elseif (PHP_OS_FAMILY === 'Linux') {
                $xrandr = shell_exec('which xrandr && xrandr 2>/dev/null');
                if ($xrandr && preg_match('/(\d+)x(\d+).*\*/', $xrandr, $matches)) {
                    $default['width'] = (int)$matches[1];
                    $default['height'] = (int)$matches[2];
                }
            } elseif (PHP_OS_FAMILY === 'Darwin') {
                $output = shell_exec('system_profiler SPDisplaysDataType 2>/dev/null');
                if (preg_match('/Resolution: (\d+) x (\d+)/', $output, $matches)) {
                    $default['width'] = (int)$matches[1];
                    $default['height'] = (int)$matches[2];
                }
            }
        } catch (Throwable $e) {
            // 返回默认值
        }

        return $default;
    }

    /**
     * 获取系统类型详细信息
     */
    public static function getSystemInfo(): array {
        return [
            'os_family' => PHP_OS_FAMILY,
            'os' => PHP_OS,
            'php_version' => PHP_VERSION,
            'architecture' => php_uname('m'),
            'hostname' => php_uname('n'),
            'sapi' => PHP_SAPI,
            'is_windows' => PHP_OS_FAMILY === 'Windows',
            'is_linux' => PHP_OS_FAMILY === 'Linux',
            'is_macos' => PHP_OS_FAMILY === 'Darwin',
        ];
    }

    /**
     * 复制到剪切板
     */
    public static function copyToClipboard(string $text): bool {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                self::executeCommand(['clip'], $text);
            } elseif (PHP_OS_FAMILY === 'Linux') {
                // 尝试xclip或xsel
                if (self::commandExists('xclip')) {
                    self::executeCommand(['xclip', '-selection', 'clipboard'], $text);
                } elseif (self::commandExists('xsel')) {
                    self::executeCommand(['xsel', '--clipboard', '--input'], $text);
                } else {
                    throw new RuntimeException('No clipboard utility found (xclip or xsel required)');
                }
            } elseif (PHP_OS_FAMILY === 'Darwin') {
                self::executeCommand(['pbcopy'], $text);
            } else {
                throw new RuntimeException('Unsupported operating system for clipboard operations');
            }

            var_dump("Text copied to clipboard", ['length' => strlen($text)]);
            return true;
        } catch (Throwable $e) {
            var_dump("Failed to copy to clipboard", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * 从剪切板获取内容
     */
    public function getFromClipboard(): ?string {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                return self::executeCommand(['powershell', '-command', 'Get-Clipboard']);
            } elseif (PHP_OS_FAMILY === 'Linux') {
                if (self::commandExists('xclip')) {
                    return self::executeCommand(['xclip', '-selection', 'clipboard', '-out']);
                } elseif (self::commandExists('xsel')) {
                    return self::executeCommand(['xsel', '--clipboard', '--output']);
                }
            } elseif (PHP_OS_FAMILY === 'Darwin') {
                return self::executeCommand(['pbpaste']);
            }

            throw new RuntimeException('Unsupported operating system for clipboard operations');
        } catch (Throwable $e) {
            var_dump("Failed to read from clipboard", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * 清空剪切板
     */
    public static function clearClipboard(): bool {
        return self::copyToClipboard('');
    }

    public static function executeCommand(array $command, string $input = null): string {
        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],  // stdin
                1 => ['pipe', 'w'],  // stdout
                2 => ['pipe', 'w']   // stderr
            ],
            $pipes
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to create process');
        }

        if ($input !== null) {
            fwrite($pipes[0], $input);
        }
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException("Command failed with exit code $exitCode: $error");
        }

        return trim($output);
    }

    public static function commandExists(string $command): bool {
        $testCommand = PHP_OS_FAMILY === 'Windows' ? "where $command" : "which $command";
        return shell_exec($testCommand) !== null;
    }

    // ========== 动态方法调用 ==========

    /**
     * 拦截未定义的静态方法调用，尝试调用扩展方法
     */
    public static function __callStatic(string $method, array $arguments)
    {
        // 检查是否有扩展方法
        if (isset(self::$extensions[$method])) {
            return call_user_func_array(self::$extensions[$method], $arguments);
        }
        
        // 对于已知的组件方法，抛出未实现的错误
        $knownComponents = [
            'window', 'vbox', 'hbox', 'grid', 'tab', 'button', 'label', 'entry',
            'checkbox', 'combobox', 'table', 'dataGrid', 'menu', 'canvas',
            'separator', 'multilineEntry', 'spinbox', 'slider', 'progressBar',
            'radio', 'group', 'passwordEntry', 'editableCombobox', 'textarea',
            'hSeparator', 'vSeparator'
        ];
        
        if (in_array($method, $knownComponents)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s() is not implemented yet',
                __CLASS__,
                $method
            ));
        }
        
        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()',
            __CLASS__,
            $method
        ));
    }

    // ========== 插件扩展方法 ==========

    /**
     * 扩展 Builder 类，添加新的组件创建方法
     * 
     * @param string $name 扩展方法名
     * @param callable $callback 回调函数，返回 ComponentBuilder 实例
     * @return void
     */
    public static function extend(string $name, callable $callback): void
    {
        self::$extensions[$name] = $callback;
    }

    /**
     * 检查是否存在扩展方法
     */
    public static function hasExtension(string $name): bool
    {
        return isset(self::$extensions[$name]);
    }

    /**
     * 获取所有扩展方法名
     */
    public static function getExtensions(): array
    {
        return array_keys(self::$extensions);
    }

    /**
     * 加载插件文件
     */
    public static function loadPlugin(string $pluginPath): bool
    {
        if (!file_exists($pluginPath)) {
            return false;
        }

        try {
            require_once $pluginPath;
            
            // 尝试自动注册
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
}