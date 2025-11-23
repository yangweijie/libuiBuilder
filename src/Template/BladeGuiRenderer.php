<?php

namespace Kingbes\Libui\View\Template;

use Exception;
use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\View\State\StateManager;

/**
 * Blade GUI 模板渲染器
 */
class BladeGuiRenderer
{
    private StateManager $state;
    private array $handlers = [];
    private array $data = [];
    private array $componentRefs = [];

    public function render(string $template, array $data = [], array $handlers = []): ComponentBuilder
    {
        $this->state = StateManager::instance();
        $this->handlers = $handlers;
        $this->data = $data;
        $this->componentRefs = [];

        // 设置数据到状态管理器
        foreach ($data as $key => $value) {
            $this->state->set($key, $value);
        }

        // 编译Blade模板为XML
        $xmlTemplate = $this->compileBladeToXml($template);

        // 使用XML渲染器渲染
        $xmlRenderer = new TemplateRenderer();
        return $xmlRenderer->render($xmlTemplate, $data, $handlers);
    }

    /**
     * 编译Blade模板为XML
     */
    private function compileBladeToXml(string $template): string
    {
        // 处理变量插值
        $template = $this->compileVariableInterpolation($template);

        // 处理条件语句
        $template = $this->compileConditionals($template);

        // 处理循环语句
        $template = $this->compileLoops($template);

        // 处理包含语句
        $template = $this->compileIncludes($template);

        // 处理组件指令
        $template = $this->compileComponents($template);

        // 处理自定义指令
        return $this->compileCustomDirectives($template);
    }

    /**
     * 编译变量插值 {{ $var }} 和 {{ var }}
     */
    private function compileVariableInterpolation(string $template): string
    {
        // 处理空合并操作符: {{ $variable ?? 'default' }}
        $template = preg_replace_callback(
            '/\{\{\s*\$([a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\]|\.[a-zA-Z0-9_]+)*)\s*\?\?\s*[\'"]([^\'"]*)[\'"]\s*\}\}/',
            function($matches) {
                $variable = $matches[1];
                $default = $matches[2];
                $value = $this->resolveVariable($variable);
                return $value !== '' ? $value : $default;
            },
            $template
        );

        // 处理 {{ $variable }} 语法
        $template = preg_replace_callback(
            '/\{\{\s*\$([a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\]|\.[a-zA-Z0-9_]+)*)\s*\}\}/',
            function($matches) {
                $variable = $matches[1];
                return $this->resolveVariable($variable);
            },
            $template
        );

        // 处理 {{ variable }} 语法 (不带 $ 符号)
        $template = preg_replace_callback(
            '/\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\]|\.[a-zA-Z0-9_]+)*)\s*\}\}/',
            function($matches) {
                $variable = $matches[1];
                return $this->resolveVariable($variable);
            },
            $template
        );

        // 处理 {{ function() }} 语法
        $template = preg_replace_callback(
            '/\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*\([^}]*\))\s*\}\}/',
            function($matches) {
                $function = $matches[1];
                return $this->evaluateFunction($function);
            },
            $template
        );

        return $template;
    }

    /**
     * 编译条件语句
     */
    private function compileConditionals(string $template): string
    {
        // @if 语句
        $template = preg_replace_callback(
            '/@if\s*\((.*?)\)(.*?)@endif/s',
            function($matches) {
                $condition = $this->compileCondition($matches[1]);
                $content = trim($matches[2]);

                if ($this->evaluateCondition($condition)) {
                    return $content;
                } else {
                    return '';
                }
            },
            $template
        );

        // @unless 语句
        $template = preg_replace_callback(
            '/@unless\s*\((.*?)\)(.*?)@endunless/s',
            function($matches) {
                $condition = $this->compileCondition($matches[1]);
                $content = trim($matches[2]);

                if (!$this->evaluateCondition($condition)) {
                    return $content;
                } else {
                    return '';
                }
            },
            $template
        );

        // @isset 语句
        $template = preg_replace_callback(
            '/@isset\s*\((.*?)\)(.*?)@endisset/s',
            function($matches) {
                $variable = trim($matches[1], '$');
                $content = trim($matches[2]);

                if (isset($this->data[$variable])) {
                    return $content;
                } else {
                    return '';
                }
            },
            $template
        );

        return $template;
    }

    /**
     * 编译循环语句
     */
    private function compileLoops(string $template): string
    {
        // @foreach 语句
        $template = preg_replace_callback(
            '/@foreach\s*\((.*?)\s+as\s+(.*?)\)(.*?)@endforeach/s',
            function($matches) {
                $collection = trim($matches[1], '$');
                $itemVariable = trim($matches[2], '$');
                $content = trim($matches[3]);

                $result = '';
                $items = $this->data[$collection] ?? [];

                foreach ($items as $index => $item) {
                    // 创建循环变量环境
                    $originalData = $this->data;
                    $loopData = $this->data;
                    $loopData[$itemVariable] = $item;
                    $loopData['loop'] = [
                        'index' => $index,
                        'first' => $index === 0,
                        'last' => $index === count($items) - 1
                    ];

                    // 设置新数据并编译内容
                    $this->data = $loopData;
                    $compiledContent = $this->compileBladeToXml($content);
                    $result .= $compiledContent;
                    
                    // 恢复原始数据
                    $this->data = $originalData;
                }

                return $result;
            },
            $template
        );

        // @for 语句
        $template = preg_replace_callback(
            '/@for\s*\((.*?)\)(.*?)@endfor/s',
            function($matches) {
                $condition = $matches[1];
                $content = trim($matches[2]);

                // 解析 for 条件 (如: $i = 0; $i < 10; $i++)
                if (preg_match('/\$(\w+)\s*=\s*(\d+);\s*\$\1\s*<\s*(\d+);\s*\$\1\+\+/', $condition, $forMatches)) {
                    $variable = $forMatches[1];
                    $start = (int)$forMatches[2];
                    $end = (int)$forMatches[3];

                    $result = '';
                    $originalData = $this->data;
                    
                    for ($i = $start; $i < $end; $i++) {
                        $loopData = $this->data;
                        $loopData[$variable] = $i;

                        $this->data = $loopData;
                        $compiledContent = $this->compileBladeToXml($content);
                        $result .= $compiledContent;
                        $this->data = $originalData;
                    }

                    return $result;
                }

                return '';
            },
            $template
        );

        return $template;
    }

    /**
     * 编译包含语句
     */
    private function compileIncludes(string $template): string
    {
        return preg_replace_callback(
            '/@include\s*\(\s*[\'"]([^\'"]+)[\'"]\s*(?:,\s*(\[.*?\]))?\s*\)/',
            function($matches) {
                $includePath = $matches[1];
                $includeData = isset($matches[2]) ? json_decode($matches[2], true) : [];

                // 查找包含文件
                $includeTemplate = $this->findIncludeTemplate($includePath);
                if ($includeTemplate) {
                    $originalData = $this->data;
                    $mergedData = array_merge($this->data, $includeData);
                    $this->data = $mergedData;
                    
                    $result = $this->compileBladeToXml($includeTemplate);
                    
                    $this->data = $originalData;
                    
                    return $result;
                }

                return '';
            },
            $template
        );
    }

    /**
     * 编译组件指令
     */
    private function compileComponents(string $template): string
    {
        // @component 指令
        $template = preg_replace_callback(
            '/@component\s*\(\s*[\'"]([^\'"]+)[\'"]\s*(?:,\s*(\[.*?\]))?\s*\)(.*?)@endcomponent/s',
            function($matches) {
                $componentName = $matches[1];
                $componentData = isset($matches[2]) ? json_decode($matches[2], true) : [];
                $slot = trim($matches[3]);

                return $this->renderComponent($componentName, $componentData, $slot);
            },
            $template
        );

        return $template;
    }

    /**
     * 编译自定义指令
     */
    private function compileCustomDirectives(string $template): string
    {
        // @csrf 指令
        $template = str_replace('@csrf', '<input type="hidden" name="_token" value="csrf_token_here"/>', $template);

        // @method 指令
        $template = preg_replace('/@method\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', '<input type="hidden" name="_method" value="$1"/>', $template);

        // @json 指令
        $template = preg_replace_callback(
            '/@json\s*\((.*?)\)/',
            function($matches) {
                $variable = trim($matches[1], '$');
                $value = $this->data[$variable] ?? null;
                return json_encode($value);
            },
            $template
        );

        return $template;
    }

    /**
     * 解析变量
     */
    private function resolveVariable(string $variable): string
    {
        // 处理数组访问 user['name'] 或 user.name
        if (strpos($variable, '[') !== false || strpos($variable, '.') !== false) {
            return $this->getNestedValue($variable);
        }

        return $this->data[$variable] ?? '';
    }

    /**
     * 获取嵌套值
     */
    private function getNestedValue(string $path): string
    {
        $keys = preg_split('/[\[\]\.]+/', $path, -1, PREG_SPLIT_NO_EMPTY);
        $value = $this->data;

        foreach ($keys as $key) {
            $key = trim($key, '\'"');
            if (is_array($value) && isset($value[$key])) {
                $value = $value[$key];
            } else {
                return '';
            }
        }

        return (string)$value;
    }

    /**
     * 编译条件表达式
     */
    private function compileCondition(string $condition): string
    {
        // 替换变量
        return preg_replace_callback(
            '/\$([a-zA-Z_][a-zA-Z0-9_]*(?:\[[^\]]+\]|\.[a-zA-Z0-9_]+)*)/',
            function($matches) {
                return '"' . $this->resolveVariable($matches[1]) . '"';
            },
            $condition
        );
    }

    /**
     * 评估条件表达式
     */
    private function evaluateCondition(string $condition): bool
    {
        // 简单的条件评估（生产环境需要更安全的实现）
        try {
            return (bool)eval("return $condition;");
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 评估函数调用
     */
    private function evaluateFunction(string $function): string
    {
        // 处理 date('Y-m-d H:i:s') 等函数调用
        if (preg_match("/^date\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/", $function, $matches)) {
            $format = $matches[1];
            return date($format);
        }
        
        // 处理 count($variable) 调用
        if (preg_match('/^count\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)/', $function, $matches)) {
            $variable = $matches[1];
            $value = $this->data[$variable] ?? [];
            return (string)count($value);
        }
        
        // 处理带空合并的 count，如 count($online_users ?? [])
        if (preg_match('/^count\s*\(\s*\{\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\?\?\s*\[\s*\]\s*\}\s*\)/', $function, $matches)) {
            $variable = $matches[1];
            $value = $this->data[$variable] ?? [];
            return (string)count($value);
        }

        return '';
    }

    /**
     * 查找包含模板
     */
    private function findIncludeTemplate(string $path): ?string
    {
        // 使用 ViewManager 的路径
        $viewManager = \Kingbes\Libui\View\ViewManager::instance();
        
        // 支持多种文件扩展名
        $extensions = ['.blade.gui', '.xml', '.gui'];
        
        foreach ($extensions as $ext) {
            $fullPath = $viewManager->findView(str_replace('.', '/', $path) . $ext);
            if ($fullPath && file_exists($fullPath)) {
                return file_get_contents($fullPath);
            }
        }

        return null;
    }

    /**
     * 渲染组件
     */
    private function renderComponent(string $name, array $data, string $slot): string
    {
        $componentTemplate = $this->findIncludeTemplate("components.{$name}");
        if ($componentTemplate) {
            $originalData = $this->data;
            $componentData = array_merge($this->data, $data, ['slot' => $slot]);
            $this->data = $componentData;

            $result = $this->compileBladeToXml($componentTemplate);

            $this->data = $originalData;
            return $result;
        }

        return '';
    }
}