<?php

namespace Kingbes\Libui\View;

use DOMDocument;
use DOMElement;
use DOMNode;
use Exception;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Components\WindowBuilder;
use Kingbes\Libui\View\Components\GridBuilder;
use Kingbes\Libui\View\Components\BoxBuilder;
use Kingbes\Libui\View\Builder\TabBuilder;
use Kingbes\Libui\View\Components\LabelBuilder;
use Kingbes\Libui\View\Components\EntryBuilder;
use Kingbes\Libui\View\Components\MultilineEntryBuilder;
use Kingbes\Libui\View\Components\ButtonBuilder;
use Kingbes\Libui\View\Components\CheckboxBuilder;
use Kingbes\Libui\View\Components\RadioBuilder;
use Kingbes\Libui\View\Components\ComboboxBuilder;
use Kingbes\Libui\View\Components\SpinboxBuilder;
use Kingbes\Libui\View\Components\SliderBuilder;
use Kingbes\Libui\View\Components\ProgressBarBuilder;
use Kingbes\Libui\View\Components\SeparatorBuilder;
use Kingbes\Libui\View\Components\TableBuilder;
use Kingbes\Libui\View\Components\CanvasBuilder;

/**
 * HTML 模板渲染器
 * 
 * 将 .ui.html 模板文件渲染为 Builder 组件树
 * 
 * 支持特性：
 * - HTML 标签到 Builder 组件的映射
 * - Grid 布局属性（row, col, rowspan, colspan, align, expand）
 * - 事件绑定（onclick, onchange 等）
 * - 双向数据绑定（bind 属性）
 * - 模板语法（{{variable}}）
 * - 组件模板复用（<template> 和 <use>）
 */
class HtmlRenderer
{
    private DOMDocument $dom;
    private array $handlers = [];
    private array $templates = [];
    private array $variables = [];
    
    /**
     * 从 HTML 文件渲染为 Builder 组件
     * 
     * @param string $htmlFile HTML 模板文件路径
     * @param array $handlers 事件处理器映射 ['handlerName' => callable]
     * @param array $variables 模板变量 ['varName' => value]
     * @return ComponentBuilder 根组件
     * @throws Exception
     */
    public static function render(
        string $htmlFile, 
        array $handlers = [], 
        array $variables = []
    ): ComponentBuilder {
        $renderer = new self();
        $renderer->handlers = $handlers;
        $renderer->variables = $variables;
        
        // 加载并解析 HTML
        $renderer->loadHtml($htmlFile);
        
        // 提取模板定义
        $renderer->extractTemplates();
        
        // 查找根元素
        $root = $renderer->findRootElement();
        
        // 递归渲染
        return $renderer->renderElement($root);
    }
    
    /**
     * 加载 HTML 文件
     */
    private function loadHtml(string $htmlFile): void
    {
        if (!file_exists($htmlFile)) {
            throw new Exception("HTML template file not found: {$htmlFile}");
        }
        
        $this->dom = new DOMDocument();
        $this->dom->preserveWhiteSpace = false;
        
        // 读取文件内容并替换模板变量，确保 UTF-8 编码
        $content = file_get_contents($htmlFile);
        
        // 确保内容是 UTF-8 编码
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        }
        
        $content = $this->replaceTemplateVariables($content);
        
        // 添加 UTF-8 编码声明并加载 HTML
        $content = '<?xml encoding="UTF-8">' . $content;
        
        // 加载 HTML（忽略 HTML5 标签警告）
        libxml_use_internal_errors(true);
        $this->dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
    }
    
    /**
     * 替换模板变量 {{variable}}
     */
    private function replaceTemplateVariables(string $content): string
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) {
            $varName = $matches[1];
            return $this->variables[$varName] ?? '';
        }, $content);
    }
    
    /**
     * 提取 <template> 定义
     */
    private function extractTemplates(): void
    {
        $templates = $this->dom->getElementsByTagName('template');
        
        foreach ($templates as $template) {
            $id = $template->getAttribute('id');
            if ($id) {
                $this->templates[$id] = $template;
            }
        }
    }
    
    /**
     * 查找根元素（通常是 <window>）
     */
    private function findRootElement(): DOMElement
    {
        // 查找 <window> 作为根元素
        $windows = $this->dom->getElementsByTagName('window');
        if ($windows->length > 0) {
            return $windows->item(0);
        }
        
        // 查找其他可能的根元素
        $rootTags = ['vbox', 'hbox', 'grid', 'tab'];
        foreach ($rootTags as $tag) {
            $elements = $this->dom->getElementsByTagName($tag);
            if ($elements->length > 0) {
                return $elements->item(0);
            }
        }
        
        throw new Exception('HTML template must contain a root element (window, vbox, hbox, grid, or tab)');
    }
    
    /**
     * 渲染单个元素
     */
    private function renderElement(DOMElement $element): ComponentBuilder
    {
        $tagName = strtolower($element->tagName);
        
        // 检查是否是 <use> 标签（模板引用）
        if ($tagName === 'use') {
            return $this->renderTemplateUse($element);
        }
        
        // 处理标准 HTML 标签的别名和特殊类型
        if ($tagName === 'select') {
            // select 是 combobox 的别名
            return $this->renderCombobox($element);
        }
        
        if ($tagName === 'progress') {
            // progress 是 progressbar 的别名
            return $this->renderProgressBar($element);
        }
        
        if ($tagName === 'hr') {
            // hr 是 separator 的别名
            return $this->renderSeparator($element);
        }
        
        if ($tagName === 'textarea') {
            // textarea 支持多行文本输入
            return $this->renderTextarea($element);
        }
        
        // 映射 HTML 标签到渲染方法（支持别名）
        $builder = match($tagName) {
            // GUI 框架特有标签
            'window' => $this->renderWindow($element),
            'grid' => $this->renderGrid($element),
            'hbox' => $this->renderHBox($element),
            'vbox' => $this->renderVBox($element),
            'tab' => $this->renderTab($element),
            
            // 标准 HTML 标签
            'label' => $this->renderLabel($element),
            'input' => $this->renderInput($element),
            'button' => $this->renderButton($element),
            'table' => $this->renderTable($element),
            'canvas' => $this->renderCanvas($element),
            
            // libuiBuilder 特有标签（保持向后兼容）
            'checkbox' => $this->renderCheckbox($element),
            'radio' => $this->renderRadio($element),
            'combobox' => $this->renderCombobox($element),
            'spinbox' => $this->renderSpinbox($element),
            'slider' => $this->renderSlider($element),
            'progressbar' => $this->renderProgressBar($element),
            'separator' => $this->renderSeparator($element),
            
            default => throw new Exception("Unknown tag: {$tagName}")
        };
        
        // 应用通用属性
        $this->applyCommonAttributes($element, $builder);
        
        return $builder;
    }
    
    /**
     * 应用通用属性（id, bind, ref, 事件等）
     */
    private function applyCommonAttributes(DOMElement $element, ComponentBuilder $builder): void
    {
        // ID
        if ($id = $element->getAttribute('id')) {
            $builder->id($id);
        }
        
        // 数据绑定 - 优先级：bind > ref
        if ($bind = $element->getAttribute('bind')) {
            $builder->bind($bind);
        } else if ($ref = $element->getAttribute('ref')) {
            // ref 属性被解释为绑定到同名状态
            $builder->bind($ref);
        }
        
        // 事件绑定
        $this->applyEventHandlers($element, $builder);
    }
    
    /**
     * 应用事件处理器
     */
    private function applyEventHandlers(DOMElement $element, ComponentBuilder $builder): void
    {
        // 支持的事件类型
        $eventMap = [
            'onclick' => 'onClick',
            'onchange' => 'onChange',
            'onselected' => 'onSelected',
            'ontoggled' => 'onToggled',
        ];
        
        foreach ($eventMap as $htmlEvent => $builderMethod) {
            if ($handlerName = $element->getAttribute($htmlEvent)) {
                if (isset($this->handlers[$handlerName])) {
                    $builder->$builderMethod($this->handlers[$handlerName]);
                }
            }
        }
    }
    
    /**
     * 渲染 <window> 元素
     */
    private function renderWindow(DOMElement $element): WindowBuilder
    {
        $builder = Builder::window();
        
        // 标题
        if ($title = $element->getAttribute('title')) {
            $builder->title($title);
        }
        
        // 尺寸
        if ($size = $element->getAttribute('size')) {
            [$width, $height] = array_map('intval', explode(',', $size));
            $builder->size($width, $height);
        }
        
        // 居中
        if ($element->getAttribute('centered') === 'true') {
            $builder->centered(true);
        }
        
        // 边距
        if ($element->getAttribute('margined') === 'true') {
            $builder->margined(true);
        }
        
        // 子元素
        $children = $this->renderChildren($element);
        if (!empty($children)) {
            $builder->contains($children);
        }
        
        return $builder;
    }
    
    /**
     * 渲染 <grid> 元素（关键实现）
     */
    private function renderGrid(DOMElement $element): GridBuilder
    {
        $builder = Builder::grid();
        
        // Grid 属性
        if ($element->getAttribute('padded') === 'true') {
            $builder->padded(true);
        }
        
        // 子元素 - Grid 需要特殊处理布局属性
        foreach ($element->childNodes as $child) {
            if (!($child instanceof DOMElement)) {
                continue;
            }
            
            // 跳过 template 标签
            if ($child->tagName === 'template') {
                continue;
            }
            
            $childBuilder = $this->renderElement($child);
            
            // 读取网格布局属性
            $row = (int)($child->getAttribute('row') ?: 0);
            $col = (int)($child->getAttribute('col') ?: 0);
            $rowspan = (int)($child->getAttribute('rowspan') ?: 1);
            $colspan = (int)($child->getAttribute('colspan') ?: 1);
            
            // 放置到 Grid
            $gridItem = $builder->place($childBuilder, $row, $col, $rowspan, $colspan);
            
            // 同时将组件添加到children数组以支持getComponentById
            $builder->addChild($childBuilder);
            
            // 对齐方式
            if ($align = $child->getAttribute('align')) {
                $alignParts = array_map('trim', explode(',', $align));
                $gridItem->align(
                    $alignParts[0] ?? 'fill',
                    $alignParts[1] ?? ($alignParts[0] ?? 'fill')
                );
            }
            
            // 扩展属性
            if ($expand = $child->getAttribute('expand')) {
                if ($expand === 'true') {
                    $gridItem->expand(true, true);
                } else if ($expand === 'horizontal' || $expand === 'h') {
                    $gridItem->expand(true, false);
                } else if ($expand === 'vertical' || $expand === 'v') {
                    $gridItem->expand(false, true);
                } else {
                    // 格式: "true,false" 或 "horizontal,vertical"
                    $expandParts = array_map('trim', explode(',', $expand));
                    $hExpand = $this->parseBoolOrDirection($expandParts[0] ?? 'false', 'horizontal');
                    $vExpand = $this->parseBoolOrDirection($expandParts[1] ?? 'false', 'vertical');
                    $gridItem->expand($hExpand, $vExpand);
                }
            }
        }
        
        return $builder;
    }
    
    /**
     * 解析布尔值或方向字符串
     */
    private function parseBoolOrDirection(string $value, string $direction): bool
    {
        $value = strtolower($value);
        if ($value === 'true') return true;
        if ($value === 'false') return false;
        if ($value === 'horizontal' || $value === 'h') return $direction === 'horizontal';
        if ($value === 'vertical' || $value === 'v') return $direction === 'vertical';
        return false;
    }
    
    /**
     * 渲染 <hbox> 元素
     */
    private function renderHBox(DOMElement $element): BoxBuilder
    {
        $builder = Builder::hbox();
        
        if ($element->getAttribute('padded') === 'true') {
            $builder->padded(true);
        }
        
        $children = $this->renderChildren($element);
        if (!empty($children)) {
            $builder->contains($children);
        }
        
        return $builder;
    }
    
    /**
     * 渲染 <vbox> 元素
     */
    private function renderVBox(DOMElement $element): BoxBuilder
    {
        $builder = Builder::vbox();
        
        if ($element->getAttribute('padded') === 'true') {
            $builder->padded(true);
        }
        
        $children = $this->renderChildren($element);
        if (!empty($children)) {
            $builder->contains($children);
        }
        
        return $builder;
    }
    
    /**
     * 渲染 <tab> 元素
     */
    private function renderTab(DOMElement $element): TabBuilder
    {
        $builder = Builder::tab();
        
        // 解析标签页
        $tabs = [];
        foreach ($element->childNodes as $child) {
            if (!($child instanceof DOMElement)) {
                continue;
            }
            
            if ($child->tagName === 'tabpage') {
                $title = $child->getAttribute('title') ?: 'Tab';
                $content = $this->renderChildren($child);
                $tabs[$title] = $content;
            }
        }
        
        if (!empty($tabs)) {
            $builder->tabs($tabs);
        }
        
        return $builder;
    }
    
    /**
     * 渲染 <label> 元素
     */
    private function renderLabel(DOMElement $element): LabelBuilder
    {
        $text = $element->textContent;
        return Builder::label()->text($text);
    }
    
    /**
     * 渲染 <input> 元素
     */
    private function renderInput(DOMElement $element): EntryBuilder|MultilineEntryBuilder|SpinboxBuilder|SliderBuilder
    {
        $type = $element->getAttribute('type');
        
        // 根据类型创建不同的输入控件
        $builder = match($type) {
            'password' => Builder::passwordEntry(),
            'multiline', 'textarea' => Builder::multilineEntry(),
            'number' => Builder::spinbox(), // 标准的数字输入框
            'range' => Builder::slider(),   // 标准的滑动条
            default => Builder::entry()
        };
        
        // 占位符（仅对文本输入框有效）
        if ($placeholder = $element->getAttribute('placeholder') && $builder instanceof EntryBuilder) {
            $builder->placeholder($placeholder);
        }
        
        // 只读（仅对文本输入框有效）
        if ($element->getAttribute('readonly') === 'true' && $builder instanceof EntryBuilder) {
            $builder->readonly(true);
        }
        
        // 多行文本特有属性
        if ($builder instanceof MultilineEntryBuilder) {
            if ($element->getAttribute('wordwrap') === 'true') {
                $builder->wordWrap(true);
            }
        }
        
        // 数字输入框特有属性
        if ($builder instanceof SpinboxBuilder) {
            if ($min = $element->getAttribute('min')) {
                $builder->min((int)$min);
            }
            if ($max = $element->getAttribute('max')) {
                $builder->max((int)$max);
            }
            if ($step = $element->getAttribute('step')) {
                $builder->step((int)$step);
            }
            if ($value = $element->getAttribute('value')) {
                $builder->value((int)$value);
            }
        }
        
        // 滑动条特有属性
        if ($builder instanceof SliderBuilder) {
            if ($min = $element->getAttribute('min')) {
                $builder->min((int)$min);
            }
            if ($max = $element->getAttribute('max')) {
                $builder->max((int)$max);
            }
            if ($value = $element->getAttribute('value')) {
                $builder->value((int)$value);
            }
        }
        
        return $builder;
    }
    
    /**
     * 渲染 <button> 元素
     */
    private function renderButton(DOMElement $element): ButtonBuilder
    {
        $text = $element->textContent;
        return Builder::button()->text($text);
    }
    
    /**
     * 渲染 <checkbox> 元素
     */
    private function renderCheckbox(DOMElement $element): CheckboxBuilder
    {
        $text = $element->textContent;
        $builder = Builder::checkbox()->text($text);
        
        if ($element->getAttribute('checked') === 'true') {
            $builder->checked(true);
        }
        
        return $builder;
    }
    
    /**
     * 渲染 <radio> 元素
     */
    private function renderRadio(DOMElement $element): RadioBuilder
    {
        $builder = Builder::radio();
        
        // 解析选项
        $items = [];
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement && $child->tagName === 'option') {
                $items[] = $child->textContent;
            }
        }
        
        if (!empty($items)) {
            $builder->items($items);
        }
        
        // 默认选中
        if ($selected = $element->getAttribute('selected')) {
            $builder->selected((int)$selected);
        }
        
        return $builder;
    }
    
    /**
     * 渲染 <combobox> 元素
     */
    private function renderCombobox(DOMElement $element): ComboboxBuilder
    {
        $builder = Builder::combobox();
        
        // 解析选项
        $items = [];
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement && $child->tagName === 'option') {
                $items[] = $child->textContent;
            }
        }
        
        if (!empty($items)) {
            $builder->items($items);
        }
        
        // 默认选中
        if ($selected = $element->getAttribute('selected')) {
            $builder->selected((int)$selected);
        }
        
        // 应用通用属性（包括数据绑定）
        $this->applyCommonAttributes($element, $builder);
        
        return $builder;
    }
    
    /**
     * 渲染 <spinbox> 元素
     */
    private function renderSpinbox(DOMElement $element): SpinboxBuilder
    {
        $builder = Builder::spinbox();
        
        // 范围
        $min = (int)($element->getAttribute('min') ?? 0);
        $max = (int)($element->getAttribute('max') ?? 100);
        $builder->range($min, $max);
        
        // 初始值
        if ($value = $element->getAttribute('value')) {
            $builder->value((int)$value);
        }
        
        // 应用通用属性（包括数据绑定）
        $this->applyCommonAttributes($element, $builder);
        
        return $builder;
    }
    
    /**
     * 渲染 <slider> 元素
     */
    private function renderSlider(DOMElement $element): SliderBuilder
    {
        $builder = Builder::slider();
        
        // 范围
        $min = (int)($element->getAttribute('min') ?? 0);
        $max = (int)($element->getAttribute('max') ?? 100);
        $builder->range($min, $max);
        
        // 初始值
        if ($value = $element->getAttribute('value')) {
            $builder->value((int)$value);
        }
        
        // 应用通用属性（包括数据绑定）
        $this->applyCommonAttributes($element, $builder);
        
        return $builder;
    }
    
    /**
     * 渲染 <progressbar> 元素
     */
    private function renderProgressBar(DOMElement $element): ProgressBarBuilder
    {
        $builder = Builder::progressBar();
        
        // 进度值
        if ($value = $element->getAttribute('value')) {
            $builder->value((int)$value);
        }
        
        return $builder;
    }
    
    /**
     * 渲染 <separator> 元素
     */
    private function renderSeparator(DOMElement $element): SeparatorBuilder
    {
        $orientation = $element->getAttribute('orientation') ?: 'horizontal';
        
        return $orientation === 'vertical' 
            ? Builder::vSeparator() 
            : Builder::hSeparator();
    }
    
    /**
     * 渲染 <table> 元素
     */
    private function renderTable(DOMElement $element): TableBuilder
    {
        $builder = Builder::table();
        
        // 提取表头信息和列类型
        $columns = [];
        $columnTypes = []; // 存储每列的类型
        $tableData = [];
        $firstDataRow = null; // 用于推断列类型
        
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->tagName === 'thead') {
                    // 从 thead 中提取列标题
                    foreach ($child->childNodes as $tr) {
                        if ($tr instanceof DOMElement && $tr->tagName === 'tr') {
                            foreach ($tr->childNodes as $th) {
                                if ($th instanceof DOMElement && $th->tagName === 'th') {
                                    $columns[] = $th->textContent;
                                }
                            }
                        }
                    }
                } elseif ($child->tagName === 'tbody') {
                    // 从 tbody 中提取第一行以推断列类型
                    $rowIndex = 0;
                    foreach ($child->childNodes as $tr) {
                        if ($tr instanceof DOMElement && $tr->tagName === 'tr') {
                            $colIndex = 0;
                            foreach ($tr->childNodes as $td) {
                                if ($td instanceof DOMElement && $td->tagName === 'td') {
                                    // 第一行：推断列类型
                                    if ($rowIndex === 0) {
                                        $columnTypes[$colIndex] = $this->detectColumnType($td);
                                    }
                                    $colIndex++;
                                }
                            }
                            $rowIndex++;
                        }
                    }
                } elseif ($child->tagName === 'column') {
                    // 兼容旧的 column 定义方式
                    $columns[] = $child->textContent;
                }
            }
        }
        
        // 构建列配置（包含类型信息）
        if (!empty($columns)) {
            $columnConfigs = [];
            foreach ($columns as $index => $title) {
                $columnConfigs[] = [
                    'title' => $title,
                    'type' => $columnTypes[$index] ?? 'text'
                ];
            }
            $builder->columns($columnConfigs);
        }
        
        // 注意：不使用 tbody 中的占位数据
        // 真实数据应该通过 bind 属性从 StateManager 获取
        
        // 应用通用属性（包括数据绑定）
        $this->applyCommonAttributes($element, $builder);
        
        // 注：数据绑定监听器已在 ComponentBuilder::bind() 中设置，
        // 不需要在这里重复设置
        
        return $builder;
    }
    
    /**
     * 检测列类型（根据 td 内容）
     * 
     * 注意：libui 的表格模型需要所有列类型一致，
     * 混合类型（checkbox + text）需要更复杂的 API。
     * 当前版本仅支持纯文本列。
     */
    private function detectColumnType(DOMElement $td): string
    {
        // TODO: 复选框列需要使用不同的表格模型 API
        // 目前禁用复选框检测，避免 segfault
        
        /* 暂时禁用
        foreach ($td->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->tagName === 'input' && $child->getAttribute('type') === 'checkbox') {
                    return 'checkbox';
                }
                if ($child->tagName === 'button') {
                    return 'button';
                }
                if ($child->tagName === 'progress' || $child->tagName === 'progressbar') {
                    return 'progress';
                }
            }
        }
        */
        
        // 默认为文本列
        return 'text';
    }
    
    /**
     * 渲染 <textarea> 元素
     */
    private function renderTextarea(DOMElement $element): MultilineEntryBuilder
    {
        $builder = Builder::multilineEntry();
        
        // 文本内容
        $text = $element->textContent;
        if ($text) {
            $builder->text($text);
        }
        
        // 占位符
        if ($placeholder = $element->getAttribute('placeholder')) {
            $builder->placeholder($placeholder);
        }
        
        // 只读
        if ($element->getAttribute('readonly') === 'true') {
            $builder->readonly(true);
        }
        
        // 自动换行
        if ($element->getAttribute('wordwrap') === 'true') {
            $builder->wordWrap(true);
        }
        
        // 行数（虽然 libui 可能不支持，但保留属性）
        if ($rows = $element->getAttribute('rows')) {
            // 可以用于其他处理
        }
        
        // 列数（虽然 libui 可能不支持，但保留属性）
        if ($cols = $element->getAttribute('cols')) {
            // 可以用于其他处理
        }
        
        // 应用通用属性（包括数据绑定）
        $this->applyCommonAttributes($element, $builder);
        
        return $builder;
    }
    
    /**
     * 渲染 <canvas> 元素
     */
    private function renderCanvas(DOMElement $element): CanvasBuilder
    {
        return Builder::canvas();
    }
    
    /**
     * 渲染模板引用 <use template="xxx" />
     */
    private function renderTemplateUse(DOMElement $element): ComponentBuilder
    {
        $templateId = $element->getAttribute('template');
        
        if (!isset($this->templates[$templateId])) {
            throw new Exception("Template not found: {$templateId}");
        }
        
        $template = $this->templates[$templateId];
        
        // 渲染模板的第一个子元素
        foreach ($template->childNodes as $child) {
            if ($child instanceof DOMElement) {
                return $this->renderElement($child);
            }
        }
        
        throw new Exception("Template {$templateId} is empty");
    }
    
    /**
     * 渲染子元素
     */
    private function renderChildren(DOMElement $element): array
    {
        $children = [];
        
        foreach ($element->childNodes as $child) {
            if (!($child instanceof DOMElement)) {
                continue;
            }
            
            // 跳过 template 和 option/column 等特殊标签
            if (in_array($child->tagName, ['template', 'option', 'column', 'tabpage'])) {
                continue;
            }
            
            $children[] = $this->renderElement($child);
        }
        
        return $children;
    }
}
