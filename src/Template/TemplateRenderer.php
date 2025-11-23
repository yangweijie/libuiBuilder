<?php

namespace Kingbes\Libui\View\Template;

use DOMElement;
use DOMText;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\View\State\StateManager;

class TemplateRenderer
{
    private StateManager $state;
    private array $handlers = [];
    private array $data = [];

    public function render(string $template, array $data = [], array $handlers = []): ComponentBuilder
    {
        $this->state = StateManager::instance();
        $this->handlers = $handlers;
        $this->data = $data;

        // 设置数据到状态管理器
        foreach ($data as $key => $value) {
            $this->state->set($key, $value);
        }

        // 判断是否为文件路径
        if ($this->isFilePath($template)) {
            $template = file_get_contents($template);
        }

        // 判断模板类型并渲染
        if ($this->isBladeTemplate($template)) {
            return $this->renderBladeTemplate($template);
        } else {
            return $this->renderXmlTemplate($template);
        }
    }

    /**
     * 渲染XML模板
     */
    private function renderXmlTemplate(string $template): ComponentBuilder
    {
        // 预处理模板变量
        $template = $this->interpolateVariables($template);

        // 解析XML
        $dom = new \DOMDocument();
        $dom->loadXML($template);

        return $this->renderElement($dom->documentElement);
    }

    /**
     * 渲染单个XML元素
     */
    private function renderElement(DOMElement $element): ComponentBuilder
    {
        $tagName = $element->tagName;
        $attributes = $this->getElementAttributes($element);

        // 创建组件
        $component = $this->createElement($tagName, $attributes);

        // 特殊处理：检查是否为不能有子元素的组件
        $isButtonOrMenuItem = ($element->tagName === 'button' || $element->tagName === 'item');
        
        // 处理普通子元素
        $children = [];
        $textContent = '';
        
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $children[] = $this->renderElement($child);
            } elseif ($child instanceof DOMText && trim($child->textContent)) {
                // 处理文本节点
                $textContent = trim($child->textContent);
            }
        }

        // 添加子元素
        if (!empty($children)) {
            if ($isButtonOrMenuItem) {
                // 按钮和菜单项不能有子元素，只处理文本内容
                if (!empty($textContent)) {
                    $component->setConfig('text', $textContent);
                }
                // 不添加子组件到按钮或菜单项
            } else {
                if (method_exists($component, 'contains')) {
                    $component->contains($children);
                } elseif (method_exists($component, 'addChild')) {
                    foreach ($children as $child) {
                        $component->addChild($child);
                    }
                }
            }
        } else if (!empty($textContent) && ($element->tagName === 'button' || $element->tagName === 'item')) {
            // 如果没有子元素但有文本内容，设置为组件文本
            $component->setConfig('text', $textContent);
        }

        return $component;
    }

    /**
     * 创建组件
     */
    private function createElement(string $tagName, array $attributes): ComponentBuilder
    {
        switch ($tagName) {
            case 'window':
                return $this->createWindow($attributes);

            case 'vbox':
                return Builder::vbox($attributes);

            case 'hbox':
                return Builder::hbox($attributes);

            case 'grid':
                return Builder::grid($attributes);

            case 'tab':
                return $this->createTab($attributes);

            case 'page':
                return Builder::vbox($attributes); // 标签页内容

            case 'form':
                return $this->createForm($attributes);

            case 'field':
                return $this->createFormField($attributes);

            case 'actions':
                return Builder::hbox($attributes);

            case 'button':
                return $this->createButton($attributes);

            case 'label':
                return Builder::label($attributes);

            case 'entry':
                return $this->createEntry($attributes);

            case 'checkbox':
                return Builder::checkbox($attributes);

            case 'table':
                return $this->createTable($attributes);

            case 'separator':
                return Builder::separator($attributes);

            case 'searchbox':
                return $this->createSearchBox($attributes);

            default:
                return Builder::vbox($attributes);
        }
    }

    /**
     * 创建窗口
     */
    private function createWindow(array $attributes): ComponentBuilder
    {
        $config = [];

        if (isset($attributes['title'])) {
            $config['title'] = $attributes['title'];
        }

        if (isset($attributes['size'])) {
            $size = explode(',', $attributes['size']);
            $config['width'] = (int)$size[0];
            $config['height'] = (int)$size[1] ?? 480;
        }

        return Builder::window($config);
    }

    /**
     * 创建标签页
     */
    private function createTab(array $attributes): ComponentBuilder
    {
        $tab = Builder::tab($attributes);

        // 处理标签页特殊逻辑在父级处理
        return $tab;
    }

    /**
     * 创建按钮
     */
    private function createButton(array $attributes): ComponentBuilder
    {
        $button = Builder::button($attributes);

        if (isset($attributes['onclick'])) {
            $handlerName = $attributes['onclick'];
            if (isset($this->handlers[$handlerName])) {
                $button->onClick($this->handlers[$handlerName]);
            }
        }

        return $button;
    }

    /**
     * 创建输入框
     */
    private function createEntry(array $attributes): ComponentBuilder
    {
        $entry = isset($attributes['type']) && $attributes['type'] === 'password'
            ? Builder::passwordEntry($attributes)
            : Builder::entry($attributes);

        if (isset($attributes['bind'])) {
            $entry->bind($attributes['bind']);
        }

        return $entry;
    }

    /**
     * 创建表格
     */
    private function createTable(array $attributes): ComponentBuilder
    {
        $config = $attributes;

        if (isset($attributes['columns'])) {
            $config['columns'] = explode(',', $attributes['columns']);
        }

        if (isset($attributes['data'])) {
            $dataKey = $attributes['data'];
            $config['data'] = $this->data[$dataKey] ?? [];
        }

        if (isset($attributes['onselect'])) {
            $handlerName = $attributes['onselect'];
            if (isset($this->handlers[$handlerName])) {
                $config['onRowSelected'] = $this->handlers[$handlerName];
            }
        }

        return Builder::table($config);
    }

    /**
     * 创建搜索框
     */
    private function createSearchBox(array $attributes): ComponentBuilder
    {
        $hbox = Builder::hbox();

        $entry = Builder::entry([
            'placeholder' => $attributes['placeholder'] ?? '搜索...'
        ]);

        $button = Builder::button(['text' => '搜索']);

        if (isset($attributes['onchange'])) {
            $handlerName = $attributes['onchange'];
            if (isset($this->handlers[$handlerName])) {
                $entry->onChange($this->handlers[$handlerName]);
            }
        }

        return $hbox->contains([$entry, $button]);
    }

    /**
     * 获取元素属性
     */
    private function getElementAttributes(DOMElement $element): array
    {
        $attributes = [];

        if ($element->hasAttributes()) {
            foreach ($element->attributes as $attr) {
                $attributes[$attr->name] = $attr->value;
            }
        }

        // 处理文本内容
        if ($element->hasChildNodes()) {
            foreach ($element->childNodes as $child) {
                if ($child instanceof DOMText && trim($child->textContent)) {
                    $attributes['text'] = trim($child->textContent);
                    break;
                }
            }
        }

        return $attributes;
    }

    /**
     * 插值变量
     */
    private function interpolateVariables(string $template): string
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function($matches) {
            $key = $matches[1];
            return $this->data[$key] ?? '';
        }, $template);
    }

    /**
     * 判断是否为文件路径
     */
    private function isFilePath(string $template): bool
    {
        return !str_contains($template, '<') &&
            (str_contains($template, '/') || str_contains($template, '.'));
    }

    /**
     * 判断是否为Blade模板
     */
    private function isBladeTemplate(string $template): bool
    {
        return str_contains($template, '@') || str_ends_with($template, '.blade.gui');
    }

    /**
     * 渲染Blade模板（复用之前的实现）
     */
    private function renderBladeTemplate(string $template): ComponentBuilder
    {
        $bladeRenderer = new BladeGuiRenderer();
        return $bladeRenderer->render($template, $this->data, $this->handlers);
    }
}