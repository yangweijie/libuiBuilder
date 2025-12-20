<?php

declare(strict_types=1);

namespace Kingbes\Libui\View;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use InvalidArgumentException;
use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\Builder\WindowBuilder;
use Kingbes\Libui\View\Builder\GridBuilder;
use Kingbes\Libui\View\Builder\ComponentBuilder;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\State\ComponentRef;
use RuntimeException;

/**
 * HTML模板渲染器 - 将HTML模板转换为libuiBuilder组件
 * 
 * 这是真正的HTML-to-GUI模板系统，支持：
 * - HTML模板解析
 * - 模板变量插值 {{variable}}
 * - 属性映射系统 (bind, onclick等)
 * - 状态管理集成
 * - 事件处理机制
 */
class HtmlRenderer
{
    private DOMDocument $dom;
    private array $templates = [];
    private array $variables = [];
    private array $eventHandlers = [];
    private StateManager $stateManager;

    public function __construct(StateManager $stateManager = null)
    {
        $this->stateManager = $stateManager ?? StateManager::instance();
        $this->dom = new DOMDocument();
    }

    /**
     * 渲染HTML模板为组件
     *
     * @param string $htmlFile HTML模板文件路径
     * @param array $eventHandlers 事件处理器映射
     * @param array $variables 模板变量
     * @return ComponentBuilder 根组件
     */
    public function render(string $htmlFile, array $eventHandlers = [], array $variables = []): ComponentBuilder
    {
        $this->eventHandlers = $eventHandlers;
        $this->variables = $variables;

        // 加载HTML文件
        $htmlContent = $this->loadHtml($htmlFile);
        
        // 解析DOM - 抑制自定义标签警告
        $previousValue = libxml_use_internal_errors(true);
        $this->dom->loadHTML($this->prepareHtml($htmlContent), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previousValue);
        
        // 替换模板变量
        $this->replaceTemplateVariables();
        
        // 提取模板定义
        $this->extractTemplates();
        
        // 查找根元素
        $rootElement = $this->findRootElement();
        
        // 渲染组件树
        return $this->renderElement($rootElement);
    }

    /**
     * 加载HTML文件内容
     */
    private function loadHtml(string $htmlFile): string
    {
        if (!file_exists($htmlFile)) {
            throw new InvalidArgumentException("HTML file not found: {$htmlFile}");
        }
        
        return file_get_contents($htmlFile);
    }

    /**
     * 准备HTML内容以避免DOM解析错误
     */
    private function prepareHtml(string $content): string
    {
        // 添加XML声明以支持HTML5标签
        if (!str_contains($content, '<?xml')) {
            $content = '<?xml encoding="UTF-8">' . $content;
        }
        
        return $content;
    }

    /**
     * 替换模板变量 {{variable}}
     */
    private function replaceTemplateVariables(): void
    {
        $xpath = new DOMXPath($this->dom);
        $textNodes = $xpath->query('//text()');
        
        foreach ($textNodes as $node) {
            $text = $node->nodeValue;
            $text = preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) {
                $key = $matches[1];
                
                // 优先级：直接变量 > 状态管理器 > 默认值
                if (isset($this->variables[$key])) {
                    return (string) $this->variables[$key];
                }
                
                if ($this->stateManager->has($key)) {
                    return (string) $this->stateManager->get($key);
                }
                
                return '';
            }, $text);
            
            $node->nodeValue = $text;
        }
    }

    /**
     * 提取模板定义 <template id="name">...</template>
     */
    private function extractTemplates(): void
    {
        $templates = $this->dom->getElementsByTagName('template');
        
        // 需要复制到数组中，因为会在循环中修改DOM
        $templateNodes = [];
        foreach ($templates as $template) {
            $templateNodes[] = $template;
        }
        
        foreach ($templateNodes as $template) {
            $id = $template->getAttribute('id');
            if ($id) {
                $this->templates[$id] = $template->cloneNode(true);
                $template->parentNode->removeChild($template);
            }
        }
    }

    /**
     * 查找根元素（第一个非空白元素）
     */
    private function findRootElement(): DOMElement
    {
        $body = $this->dom->getElementsByTagName('body')->item(0);
        
        if (!$body) {
            throw new RuntimeException('No body element found in HTML');
        }
        
        foreach ($body->childNodes as $child) {
            if ($child instanceof DOMElement && $child->nodeName !== 'script') {
                return $child;
            }
        }
        
        throw new RuntimeException('No root element found in HTML body');
    }

    /**
     * 递归渲染DOM元素为组件
     */
    private function renderElement(DOMElement $element): ?ComponentBuilder
    {
        $tagName = strtolower($element->nodeName);
        
        echo "[HTML_DEBUG] 渲染元素: <{$tagName}>\n";
        
        // 处理 <use> 标签
        if ($tagName === 'use') {
            return $this->renderUseElement($element);
        }
        
        // 创建对应的Builder
        $builder = $this->createBuilderForTag($tagName);
        
        // 如果是特殊标签（如 option），返回 null 或处理特殊情况
        if ($builder === null) {
            echo "[HTML_DEBUG] 元素 <{$tagName}> 返回 null\n";
            // 对于 option 标签，返回父组件处理
            return null;
        }
        
        echo "[HTML_DEBUG] 创建了 {$tagName} Builder: " . get_class($builder) . "\n";
        
        // 设置 StateManager 和 EventDispatcher (必须在 bind 之前)
        $builder->setStateManager($this->stateManager);
        
        // 特殊处理：input 根据类型重新创建
        if ($tagName === 'input') {
            $type = strtolower($element->getAttribute('type') ?? 'text');
            echo "[HTML_DEBUG] 重新创建 input type: {$type}\n";
            
            switch ($type) {
                case 'number':
                    $builder = Builder::spinbox();
                    break;
                case 'range':
                    $builder = Builder::slider();
                    break;
                case 'password':
                    // 暂时使用 entry，后续可以添加密码组件
                    $builder = Builder::entry();
                    break;
                default:
                    $builder = Builder::entry();
            }
            
            // 重新设置 StateManager
            $builder->setStateManager($this->stateManager);
        }
        
        // 应用通用属性
        $this->applyCommonAttributes($element, $builder);
        
        // 应用特定属性
        $this->applySpecificAttributes($element, $builder);
        
        // 渲染子元素
        $this->renderChildren($element, $builder);
        
        return $builder;
    }

    /**
     * 处理 <use> 标签引用模板
     */
    private function renderUseElement(DOMElement $element): ?ComponentBuilder
    {
        $templateId = $element->getAttribute('ref');
        if (!$templateId || !isset($this->templates[$templateId])) {
            throw new InvalidArgumentException("Template not found: {$templateId}");
        }
        
        $template = $this->templates[$templateId];
        $templateElement = $template->firstElementChild;
        
        if (!$templateElement) {
            throw new RuntimeException("Template {$templateId} is empty");
        }
        
        // 克隆模板元素
        $clonedElement = $templateElement->cloneNode(true);
        
        // 应用use元素的属性到模板元素
        foreach ($element->attributes as $attr) {
            if ($attr->nodeName !== 'ref') {
                $clonedElement->setAttribute($attr->nodeName, $attr->nodeValue);
            }
        }
        
        return $this->renderElement($clonedElement);
    }

    /**
     * 根据标签名创建对应的Builder
     */
    private function createBuilderForTag(string $tagName): ?ComponentBuilder
    {
        return match($tagName) {
            // 主要布局组件
            'window' => Builder::window(),
            'grid' => Builder::grid(),
            'hbox' => Builder::hbox(),
            'vbox' => Builder::vbox(),
            'box' => Builder::vbox(), // 默认垂直布局
            
            // 基础组件
            'label' => Builder::label(),
            'button' => Builder::button(),
            'input' => $this->createInputBuilder(),
            'entry' => Builder::entry(), // 支持直接使用 entry 标签
            'textarea' => Builder::entry()->multiline(), // HTML textarea 映射到多行 entry
            
            // 选择组件
            'checkbox' => Builder::checkbox(),
            'combobox' => Builder::combobox(),
            'radio' => $this->createRadioBuilder(), // 单选框组
            
            // 数值组件
            'slider' => Builder::slider(),
            'spinbox' => Builder::spinbox(),
            'number' => Builder::spinbox(), // 别名
            
            // 显示组件
            'progressbar' => Builder::progress(),
            'progress' => Builder::progress(), // 别名
            'separator' => Builder::separator(),
            'hr' => Builder::separator(), // HTML 兼容
            
            // 容器组件
            'group' => Builder::group(),
            'fieldset' => Builder::group(), // HTML 兼容
            'table' => Builder::table(),
            
            // 文本组件（HTML 兼容）
            'span' => Builder::label(),
            'p' => Builder::label(),
            'h1' => Builder::label(),
            'h2' => Builder::label(),
            'h3' => Builder::label(),
            'h4' => Builder::label(),
            'h5' => Builder::label(),
            'h6' => Builder::label(),
            
            // 特殊标签（在子元素处理中忽略）
            'option' => null, // 在 combobox 中处理
            'template' => null, // 模板标签在提取时处理
            'use' => null, // use 标签在 renderUseElement 中处理
            
            default => throw new InvalidArgumentException("Unsupported tag: {$tagName}")
        };
    }

    /**
     * 根据input类型创建对应的Builder
     * 注意：这个方法在DOM元素创建时调用，但type属性在后续处理中获取
     * 所以这里返回默认的entry，然后在renderElement中根据type重新创建
     */
    private function createInputBuilder(): ComponentBuilder
    {
        return Builder::entry(); // 默认返回entry，后续会根据type调整
    }

    /**
     * 创建单选框构建器（使用多个checkbox模拟）
     */
    private function createRadioBuilder(): ComponentBuilder
    {
        // 暂时使用 checkbox 作为替代
        return Builder::checkbox();
    }

    /**
     * 应用通用属性
     */
    private function applyCommonAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        $tagName = strtolower($element->nodeName);
        echo "[HTML_DEBUG] 处理 <{$tagName}> 元素的通用属性\n";
        
        // ID属性
        if ($id = $element->getAttribute('id')) {
            echo "[HTML_DEBUG]   - 设置 ID: {$id}\n";
            $builder->id($id);
        }
        
        // 数据绑定
        if ($bind = $element->getAttribute('bind')) {
            echo "[HTML_DEBUG]   - 设置数据绑定: {$bind}\n";
            $builder->bind($bind);
        }
        
        // 事件处理
        $this->applyEventHandlers($element, $builder);
    }

    /**
     * 应用特定属性
     */
    private function applySpecificAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        $tagName = strtolower($element->nodeName);
        echo "[HTML_DEBUG] 处理 <{$tagName}> 元素的特定属性\n";
        
        // 输出所有属性
        foreach ($element->attributes as $attr) {
            echo "[HTML_DEBUG]   - 属性: {$attr->nodeName}=\"{$attr->nodeValue}\"\n";
        }
        
        match($tagName) {
            // 布局组件
            'window' => $this->applyWindowAttributes($element, $builder),
            'grid' => $this->applyGridAttributes($element, $builder),
            'hbox' => $this->applyBoxAttributes($element, $builder, 'horizontal'),
            'vbox' => $this->applyBoxAttributes($element, $builder, 'vertical'),
            'box' => $this->applyBoxAttributes($element, $builder, 'vertical'),
            
            // 基础组件（文本类）
            'label' => $this->applyLabelAttributes($element, $builder),
            'span' => $this->applyLabelAttributes($element, $builder),
            'p' => $this->applyLabelAttributes($element, $builder),
            'h1' => $this->applyLabelAttributes($element, $builder),
            'h2' => $this->applyLabelAttributes($element, $builder),
            'h3' => $this->applyLabelAttributes($element, $builder),
            'h4' => $this->applyLabelAttributes($element, $builder),
            'h5' => $this->applyLabelAttributes($element, $builder),
            'h6' => $this->applyLabelAttributes($element, $builder),
            
            // 输入组件
            'input' => $this->applyInputAttributes($element, $builder),
            'entry' => $this->applyInputAttributes($element, $builder),
            'button' => $this->applyButtonAttributes($element, $builder),
            
            // 选择组件
            'checkbox' => $this->applyCheckboxAttributes($element, $builder),
            'combobox' => $this->applyComboboxAttributes($element, $builder),
            'radio' => $this->applyCheckboxAttributes($element, $builder),
            
            // 数值组件
            'slider' => $this->applySliderAttributes($element, $builder),
            'spinbox' => $this->applySpinboxAttributes($element, $builder),
            'number' => $this->applySpinboxAttributes($element, $builder),
            
            // 显示组件
            'progressbar' => $this->applyProgressbarAttributes($element, $builder),
            'progress' => $this->applyProgressbarAttributes($element, $builder),
            'separator' => $this->applySeparatorAttributes($element, $builder),
            'hr' => $this->applySeparatorAttributes($element, $builder),
            
            // 容器组件
            'group' => $this->applyGroupAttributes($element, $builder),
            'fieldset' => $this->applyGroupAttributes($element, $builder),
            'table' => $this->applyTableAttributes($element, $builder),
            
            default => null
        };
    }

    /**
     * 应用窗口属性
     */
    private function applyWindowAttributes(DOMElement $element, WindowBuilder $builder): void
    {
        if ($title = $element->getAttribute('title')) {
            $builder->title($title);
        }
        
        // 优先处理 size 属性（格式：size="400,300"）
        if ($size = $element->getAttribute('size')) {
            $dimensions = array_map('trim', explode(',', $size));
            if (count($dimensions) >= 2) {
                $builder->size((int)$dimensions[0], (int)$dimensions[1]);
            }
        } elseif ($width = $element->getAttribute('width')) {
            // 兼容单独的 width 和 height 属性
            if ($height = $element->getAttribute('height')) {
                $builder->size((int)$width, (int)$height);
            }
        }
        
        // 暂时注释掉centered方法，因为WindowBuilder可能没有这个方法
        // if ($element->getAttribute('centered') !== null) {
        //     $builder->centered();
        // }
        
        if ($element->getAttribute('margined') !== null) {
            $builder->margined(true);
        }
    }

    /**
     * 应用Grid属性
     */
    private function applyGridAttributes(DOMElement $element, GridBuilder $builder): void
    {
        if ($element->getAttribute('padded') !== null) {
            echo "[HTML_DEBUG]   - Grid属性: padded\n";
            $builder->padded(true);
        }
        
        // 设置列数（如果指定）
        if ($columns = $element->getAttribute('columns')) {
            echo "[HTML_DEBUG]   - Grid列数: {$columns}\n";
            $builder->columns((int)$columns);
        }
        
        // 注意：Grid 的扩展属性需要在添加到父容器时设置
        // 这里我们存储这些属性供后续使用
        if ($element->getAttribute('hexpand') !== null) {
            echo "[HTML_DEBUG]   - Grid属性: hexpand\n";
            $builder->setConfig('hexpand', true);
        }
        
        if ($element->getAttribute('vexpand') !== null) {
            echo "[HTML_DEBUG]   - Grid属性: vexpand\n";
            $builder->setConfig('vexpand', true);
        }
    }

    /**
     * 应用标签属性
     */
    private function applyLabelAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        if ($text = trim($element->textContent)) {
            echo "[HTML_DEBUG]   - 标签文本: \"{$text}\"\n";
            $builder->text($text);
        }
        
        // 检查扩展属性
        if ($element->getAttribute('hexpand') !== null) {
            echo "[HTML_DEBUG]   - 标签属性: hexpand\n";
        }
        if ($element->getAttribute('vexpand') !== null) {
            echo "[HTML_DEBUG]   - 标签属性: vexpand\n";
        }
        if ($align = $element->getAttribute('align')) {
            echo "[HTML_DEBUG]   - 标签对齐: {$align}\n";
        }
    }

    /**
     * 应用输入框属性
     */
    private function applyInputAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        $type = strtolower($element->getAttribute('type') ?? 'text');
        echo "[HTML_DEBUG]   - input type: {$type}\n";
        
        // 根据类型设置不同的属性
        switch ($type) {
            case 'text':
                $placeholder = $element->getAttribute('placeholder');
                if ($placeholder !== null && $placeholder !== false && method_exists($builder, 'placeholder')) {
                    $builder->placeholder((string)$placeholder);
                }
                break;
                
            case 'password':
                // 对于密码输入，libui 可能需要特殊处理
                $placeholder = $element->getAttribute('placeholder');
                if ($placeholder !== null && $placeholder !== false && method_exists($builder, 'placeholder')) {
                    $builder->placeholder((string)$placeholder);
                }
                // TODO: 实现密码掩码
                break;
                
            case 'number':
                // 数字输入需要转换为 spinbox
                if ($min = $element->getAttribute('min')) {
                    if ($max = $element->getAttribute('max')) {
                        $builder->range((int)$min, (int)$max);
                    }
                }
                if ($value = $element->getAttribute('value')) {
                    $builder->value((int)$value);
                }
                break;
                
            case 'range':
                // 范围输入需要转换为 slider
                if ($min = $element->getAttribute('min')) {
                    if ($max = $element->getAttribute('max')) {
                        $builder->range((int)$min, (int)$max);
                    }
                }
                if ($value = $element->getAttribute('value')) {
                    $builder->value((int)$value);
                }
                break;
        }
        
        // 通用属性 - 只在组件支持的情况下调用
        $readonly = $element->getAttribute('readonly');
        if ($readonly !== null && !empty($readonly) && method_exists($builder, 'readonly')) {
            $builder->readonly();
        }
    }

    /**
     * 应用按钮属性
     */
    private function applyButtonAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        if ($text = trim($element->textContent)) {
            echo "[HTML_DEBUG]   - 按钮文本: \"{$text}\"\n";
            $builder->text($text);
        }
        
        // 检查扩展属性
        if ($element->getAttribute('stretchy') !== null) {
            echo "[HTML_DEBUG]   - 按钮属性: stretchy\n";
        }
        if ($element->getAttribute('hexpand') !== null) {
            echo "[HTML_DEBUG]   - 按钮属性: hexpand\n";
        }
        if ($element->getAttribute('vexpand') !== null) {
            echo "[HTML_DEBUG]   - 按钮属性: vexpand\n";
        }
        if ($align = $element->getAttribute('align')) {
            echo "[HTML_DEBUG]   - 按钮对齐: {$align}\n";
        }
    }

    /**
     * 应用复选框属性
     */
    private function applyCheckboxAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        if ($text = trim($element->textContent)) {
            $builder->text($text);
        }
        
        if ($element->getAttribute('checked') !== null) {
            $builder->checked(true);
        }
    }

    /**
     * 应用下拉框属性
     */
    private function applyComboboxAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        // 解析options子元素 - 只获取直接子元素
        $options = [];
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->nodeName) === 'option') {
                $optionText = trim($child->textContent);
                $optionValue = $child->getAttribute('value');
                
                // 如果没有 value 属性，使用文本内容作为选项值
                $optionValue = $optionValue !== null && $optionValue !== '' ? $optionValue : $optionText;
                
                $options[] = $optionValue;
                echo "[HTML_DEBUG]   - 添加选项: '{$optionValue}'\n";
            }
        }
        
        if (!empty($options)) {
            echo "[HTML_DEBUG]   - 设置 " . count($options) . " 个选项到 combobox\n";
            $builder->items($options);
        } else {
            echo "[HTML_DEBUG]   - 警告: combobox 没有找到选项\n";
        }
    }

    /**
     * 应用滑块属性
     */
    private function applySliderAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        if ($min = $element->getAttribute('min')) {
            if ($max = $element->getAttribute('max')) {
                $builder->range((int)$min, (int)$max);
            }
        }
        
        if ($value = $element->getAttribute('value')) {
            $builder->value((int)$value);
        }
    }

    /**
     * 应用进度条属性
     */
    private function applyProgressbarAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        if ($value = $element->getAttribute('value')) {
            $builder->value((int)$value);
        }
    }

    /**
     * 应用组属性
     */
    private function applyGroupAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        if ($title = $element->getAttribute('title')) {
            $builder->title($title);
        }
    }

    /**
     * 应用事件处理器
     */
    private function applyEventHandlers(DOMElement $element, ComponentBuilder $builder): void
    {
        foreach ($element->attributes as $attr) {
            $attrName = strtolower($attr->nodeName);
            
            if (str_starts_with($attrName, 'on')) {
                $eventType = substr($attrName, 2);
                $handlerName = $attr->nodeValue;
                
                echo "[HTML_DEBUG]   - 事件处理器: {$attrName} -> {$handlerName} (事件类型: {$eventType})\n";
                
                if (isset($this->eventHandlers[$handlerName])) {
                    $handler = $this->eventHandlers[$handlerName];
                    echo "[HTML_DEBUG]   - 绑定事件处理器成功: {$handlerName}\n";
                    $builder->on($eventType, $handler);
                } else {
                    echo "[HTML_DEBUG]   - 警告: 事件处理器未找到: {$handlerName}\n";
                }
            }
        }
    }

    /**
     * 渲染子元素
     */
    private function renderChildren(DOMElement $element, ComponentBuilder $builder): void
    {
        $children = [];
        
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $childBuilder = $this->renderElement($child);
                
                // 跳过 null 返回值（如 option 标签）
                if ($childBuilder === null) {
                    continue;
                }
                
                // 处理Grid布局的特殊属性
                if ($builder instanceof GridBuilder) {
                    $this->applyGridLayoutAttributes($child, $childBuilder, $builder);
                    // Grid 子元素已经通过 place 方法添加，不需要再添加到 children 数组
                } else {
                    $children[] = $childBuilder;
                }
            }
        }
        
        if (!empty($children)) {
            foreach ($children as $child) {
                // 根据不同的 Builder 类型使用不同的 append 方法
                if (method_exists($builder, 'append') && $builder instanceof \Kingbes\Libui\View\Builder\GridBuilder) {
                    // GridBuilder 的 append 方法有 5 个参数
                    $builder->append($child, 0, 0, 1, 1);
                } elseif (method_exists($builder, 'append')) {
                    // 其他 Builder 的 append 方法通常只有 2 个参数
                    $builder->append($child, false);
                } else {
                    // 如果没有 append 方法，尝试其他添加子元素的方法
                    if (method_exists($builder, 'contains')) {
                        $builder->contains($child);
                    }
                }
            }
        }
    }

    /**
     * 应用Grid布局属性
     */
    private function applyGridLayoutAttributes(DOMElement $element, ComponentBuilder $builder, ?GridBuilder $gridBuilder = null): void
    {
        if (!$gridBuilder) {
            return;
        }
        
        $tagName = strtolower($element->nodeName);
        echo "[HTML_DEBUG] Grid布局: 处理 <{$tagName}> 元素\n";
        
        $row = (int)($element->getAttribute('row') ?: 0);
        $col = (int)($element->getAttribute('col') ?: 0);
        $rowspan = (int)($element->getAttribute('rowspan') ?: 1);
        $colspan = (int)($element->getAttribute('colspan') ?: 1);
        
        echo "[HTML_DEBUG]   - 位置: row={$row}, col={$col}, rowspan={$rowspan}, colspan={$colspan}\n";
        
        // 检查当前元素是否需要水平扩展
        $hexpandValue = $element->getAttribute('hexpand');
        if ($hexpandValue !== null) {
            $hexpand = !in_array(strtolower($hexpandValue), ['false', '0', 'no']);
        } else {
            $hexpand = false; // 默认不扩展
        }
        
        // 检查当前元素是否需要垂直扩展  
        $vexpandValue = $element->getAttribute('vexpand');
        if ($vexpandValue !== null) {
            $vexpand = !in_array(strtolower($vexpandValue), ['false', '0', 'no']);
        } else {
            $vexpand = false; // 默认不扩展
        }
        
        // 根据元素类型设置默认的对齐方式
        $halign = 'fill';  // 默认水平填充
        $valign = 'fill';  // 默认垂直填充
        
        // 标签默认不扩展，左对齐，垂直居中
        if ($element->tagName === 'label') {
            // 标签的扩展属性已经在上面正确解析了
            // 这里不需要覆盖
            // 检查明确的 align 属性
            if ($element->getAttribute('align') !== null) {
                $alignValue = strtolower($element->getAttribute('align'));
                $halign = match($alignValue) {
                    'left', 'start' => 'start',
                    'center' => 'center',
                    'right', 'end' => 'end',
                    default => 'start'
                };
                $valign = 'center';
            } else {
                $halign = 'start';  // 默认左对齐
                $valign = 'center'; // 默认垂直居中
            }
        }
        
        // 分隔符默认水平扩展，但不垂直扩展
        if ($element->tagName === 'separator') {
            // 如果未明确设置，使用默认值
            if ($element->getAttribute('hexpand') === null) {
                $hexpand = true;  // 默认水平扩展
            }
            if ($element->getAttribute('vexpand') === null) {
                $vexpand = false; // 默认不垂直扩展
            }
            $halign = 'fill';
            $valign = 'center';
        }
        
        // 按钮默认行为
        if ($element->tagName === 'button') {
            // 如果未明确设置，使用默认值
            if ($element->getAttribute('hexpand') === null) {
                $hexpand = true;  // 默认水平扩展
            }
            if ($element->getAttribute('vexpand') === null) {
                $vexpand = false; // 默认不垂直扩展
            }
            if ($element->getAttribute('align') !== null) {
                $alignValue = strtolower($element->getAttribute('align'));
                $halign = match($alignValue) {
                    'left', 'start' => 'start',
                    'center' => 'center',
                    'right', 'end' => 'end',
                    'fill' => 'fill',
                    default => 'fill'
                };
            } else {
                $halign = 'fill';  // 默认填充
            }
            $valign = 'center';
        }
        
        // 盒子默认水平和垂直扩展
        if ($element->tagName === 'hbox') {
            $hexpand = $hexpand ?: true;
            $halign = 'fill';
            $valign = 'fill';  // 盒子可以垂直扩展
        }
        
        echo "[HTML_DEBUG]   - 扩展设置: hexpand=" . ($hexpand ? 'true' : 'false') . ", vexpand=" . ($vexpand ? 'true' : 'false') . "\n";
        echo "[HTML_DEBUG]   - 对齐设置: halign={$halign}, valign={$valign}\n";
        
        // 使用 append 方法，支持扩展属性
        $gridBuilder->append(
            $builder,
            $row,
            $col,
            $rowspan,
            $colspan,
            $hexpand,
            $halign,
            $vexpand,
            $valign
        );
        
        echo "[HTML_DEBUG]   - Grid append 调用完成\n";
    }

    /**
     * 应用盒子属性
     */
    private function applyBoxAttributes(DOMElement $element, ComponentBuilder $builder, string $defaultDirection): void
    {
        if ($element->getAttribute('padded') !== null) {
            $builder->padded(true);
        }
        
        if ($direction = $element->getAttribute('direction')) {
            $builder->direction($direction);
        }
    }

    /**
     * 应用分隔线属性
     */
    private function applySeparatorAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        // 分隔线通常不需要特殊属性
        // 可以在这里添加未来需要的属性处理
    }

    /**
     * 应用数字输入框属性
     */
    private function applySpinboxAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        if ($min = $element->getAttribute('min')) {
            if ($max = $element->getAttribute('max')) {
                $builder->range((int)$min, (int)$max);
            }
        }
        
        if ($value = $element->getAttribute('value')) {
            $builder->value((int)$value);
        }
        
        if ($element->getAttribute('readonly') !== null) {
            // Spinbox 可能需要 readonly 属性处理
        }
    }

    /**
     * 应用表格属性
     */
    private function applyTableAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        // 表格属性处理
        if ($columns = $element->getAttribute('columns')) {
            $columnNames = explode(',', $columns);
            $builder->columns(array_map('trim', $columnNames));
        }
    }
}