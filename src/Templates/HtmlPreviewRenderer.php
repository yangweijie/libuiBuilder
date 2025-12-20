<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Templates;

use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Builder\ComponentBuilder;

/**
 * HTML预览渲染器 - 将libuiBuilder组件转换为HTML
 * 
 * 支持将GUI组件渲染为HTML格式，便于Web预览和调试
 * 注意：这是GUI-to-HTML的预览工具，不是HTML-to-GUI的模板系统
 */
class HtmlPreviewRenderer
{
    private array $components = [];
    private array $styles = [];
    private array $scripts = [];

    public function __construct()
    {
        $this->initDefaultStyles();
    }

    /**
     * 渲染组件为HTML
     */
    public function render(ComponentBuilder $component): string
    {
        $this->components = [];
        $this->extractComponents($component);
        
        return $this->buildHtml();
    }

    /**
     * 提取组件信息
     */
    private function extractComponents(ComponentBuilder $component, string $parentId = null): array
    {
        $id = $component->getId() ?? uniqid('comp_');
        $type = $component->getType();
        $config = $component->getConfig();

        $componentInfo = [
            'id' => $id,
            'type' => $type,
            'config' => $config,
            'parentId' => $parentId,
            'children' => []
        ];

        // 提取子组件
        if (isset($config['children']) && is_array($config['children'])) {
            foreach ($config['children'] as $child) {
                if ($child instanceof ComponentBuilder) {
                    $componentInfo['children'][] = $this->extractComponents($child, $id);
                }
            }
        }

        $this->components[$id] = $componentInfo;
        return $componentInfo;
    }

    /**
     * 构建完整HTML
     */
    private function buildHtml(): string
    {
        $html = '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>libuiBuilder Preview</title>
    <style>
        ' . $this->buildStyles() . '
    </style>
</head>
<body>
    <div class="libui-preview">
        ' . $this->renderComponents() . '
    </div>
    <script>
        ' . $this->buildScripts() . '
    </script>
</body>
</html>';

        return $html;
    }

    /**
     * 渲染所有组件
     */
    private function renderComponents(): string
    {
        $html = '';
        
        // 找到根组件
        $rootComponents = array_filter($this->components, fn($comp) => empty($comp['parentId']));
        
        foreach ($rootComponents as $component) {
            $html .= $this->renderComponent($component);
        }
        
        return $html;
    }

    /**
     * 渲染单个组件
     */
    private function renderComponent(array $component): string
    {
        $type = $component['type'];
        $config = $component['config'];
        $id = $component['id'];
        
        switch ($type) {
            case 'window':
                return $this->renderWindow($component);
            case 'vbox':
            case 'hbox':
                return $this->renderBox($component);
            case 'button':
                return $this->renderButton($component);
            case 'label':
                return $this->renderLabel($component);
            case 'entry':
                return $this->renderEntry($component);
            case 'checkbox':
                return $this->renderCheckbox($component);
            case 'combobox':
                return $this->renderCombobox($component);
            case 'slider':
                return $this->renderSlider($component);
            case 'spinbox':
                return $this->renderSpinbox($component);
            case 'progressbar':
                return $this->renderProgressbar($component);
            case 'separator':
                return $this->renderSeparator($component);
            case 'group':
                return $this->renderGroup($component);
            case 'grid':
                return $this->renderGrid($component);
            case 'tab':
                return $this->renderTab($component);
            case 'table':
                return $this->renderTable($component);
            default:
                return '<div class="component unknown" data-id="' . $id . '">Unknown component: ' . $type . '</div>';
        }
    }

    /**
     * 渲染窗口
     */
    private function renderWindow(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        
        $title = $config['title'] ?? 'Window';
        $width = $config['width'] ?? 800;
        $height = $config['height'] ?? 600;
        
        $childrenHtml = '';
        foreach ($component['children'] as $child) {
            $childrenHtml .= $this->renderComponent($child);
        }
        
        return '<div class="window" data-id="' . $id . '" style="width: ' . $width . 'px; height: ' . $height . 'px;">
            <div class="window-title">' . htmlspecialchars($title) . '</div>
            <div class="window-content">
                ' . $childrenHtml . '
            </div>
        </div>';
    }

    /**
     * 渲染盒子布局
     */
    private function renderBox(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        $type = $component['type'];
        
        $orientation = $type === 'vbox' ? 'vertical' : 'horizontal';
        $class = 'box box-' . $orientation;
        
        $childrenHtml = '';
        foreach ($component['children'] as $child) {
            $childrenHtml .= $this->renderComponent($child);
        }
        
        return '<div class="' . $class . '" data-id="' . $id . '">' . $childrenHtml . '</div>';
    }

    /**
     * 渲染按钮
     */
    private function renderButton(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        
        $text = $config['text'] ?? 'Button';
        $enabled = $config['enabled'] ?? true;
        
        return '<button class="button" data-id="' . $id . '" ' . ($enabled ? '' : 'disabled') . '>' . 
               htmlspecialchars($text) . '</button>';
    }

    /**
     * 渲染标签
     */
    private function renderLabel(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        
        $text = $config['text'] ?? 'Label';
        
        return '<div class="label" data-id="' . $id . '">' . htmlspecialchars($text) . '</div>';
    }

    /**
     * 渲染输入框
     */
    private function renderEntry(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        
        $placeholder = $config['placeholder'] ?? '';
        $value = $config['value'] ?? '';
        $readonly = $config['readonly'] ?? false;
        
        return '<input type="text" class="entry" data-id="' . $id . '" 
                placeholder="' . htmlspecialchars($placeholder) . '" 
                value="' . htmlspecialchars($value) . '" 
                ' . ($readonly ? 'readonly' : '') . ' />';
    }

    /**
     * 渲染复选框
     */
    private function renderCheckbox(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        
        $text = $config['text'] ?? '';
        $checked = $config['checked'] ?? false;
        
        return '<label class="checkbox" data-id="' . $id . '">
            <input type="checkbox" ' . ($checked ? 'checked' : '') . ' />
            <span>' . htmlspecialchars($text) . '</span>
        </label>';
    }

    /**
     * 渲染下拉框
     */
    private function renderCombobox(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        
        $items = $config['items'] ?? [];
        $selected = $config['selected'] ?? 0;
        
        $optionsHtml = '';
        foreach ($items as $index => $item) {
            $selectedAttr = $index === $selected ? 'selected' : '';
            $optionsHtml .= '<option value="' . $index . '" ' . $selectedAttr . '>' . 
                           htmlspecialchars($item) . '</option>';
        }
        
        return '<select class="combobox" data-id="' . $id . '">' . $optionsHtml . '</select>';
    }

    /**
     * 渲染滑块
     */
    private function renderSlider(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        
        $min = $config['min'] ?? 0;
        $max = $config['max'] ?? 100;
        $value = $config['value'] ?? 50;
        
        return '<div class="slider-container" data-id="' . $id . '">
            <input type="range" class="slider" min="' . $min . '" max="' . $max . '" value="' . $value . '" />
            <span class="slider-value">' . $value . '</span>
        </div>';
    }

    /**
     * 渲染数字输入框
     */
    private function renderSpinbox(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        
        $min = $config['min'] ?? 0;
        $max = $config['max'] ?? 100;
        $value = $config['value'] ?? 0;
        
        return '<div class="spinbox-container" data-id="' . $id . '">
            <button class="spinbox-down">-</button>
            <input type="number" class="spinbox" min="' . $min . '" max="' . $max . '" value="' . $value . '" />
            <button class="spinbox-up">+</button>
        </div>';
    }

    /**
     * 渲染进度条
     */
    private function renderProgressbar(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        
        $value = $config['value'] ?? 0;
        
        return '<div class="progressbar" data-id="' . $id . '">
            <div class="progressbar-fill" style="width: ' . $value . '%"></div>
            <span class="progressbar-text">' . $value . '%</span>
        </div>';
    }

    /**
     * 渲染分隔线
     */
    private function renderSeparator(array $component): string
    {
        $id = $component['id'];
        
        return '<hr class="separator" data-id="' . $id . '" />';
    }

    /**
     * 渲染组容器
     */
    private function renderGroup(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        
        $title = $config['title'] ?? 'Group';
        
        $childrenHtml = '';
        foreach ($component['children'] as $child) {
            $childrenHtml .= $this->renderComponent($child);
        }
        
        return '<fieldset class="group" data-id="' . $id . '">
            <legend>' . htmlspecialchars($title) . '</legend>
            ' . $childrenHtml . '
        </fieldset>';
    }

    /**
     * 渲染网格布局
     */
    private function renderGrid(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        
        $columns = $config['columns'] ?? 2;
        
        $childrenHtml = '';
        foreach ($component['children'] as $child) {
            $childrenHtml .= '<div class="grid-item">' . $this->renderComponent($child) . '</div>';
        }
        
        return '<div class="grid" data-id="' . $id . '" style="grid-template-columns: repeat(' . $columns . ', 1fr);">
            ' . $childrenHtml . '
        </div>';
    }

    /**
     * 渲染标签页
     */
    private function renderTab(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        
        $tabs = $config['tabs'] ?? [];
        
        $tabsHtml = '';
        $contentHtml = '';
        
        foreach ($tabs as $index => $tab) {
            $active = $index === 0 ? 'active' : '';
            $tabsHtml .= '<button class="tab-button ' . $active . '" data-tab="' . $index . '">' . 
                        htmlspecialchars($tab['title']) . '</button>';
            $contentHtml .= '<div class="tab-content ' . $active . '" data-content="' . $index . '">
                ' . (isset($tab['content']) ? $this->renderComponent($tab['content']) : '') . '
            </div>';
        }
        
        return '<div class="tab" data-id="' . $id . '">
            <div class="tab-header">' . $tabsHtml . '</div>
            <div class="tab-body">' . $contentHtml . '</div>
        </div>';
    }

    /**
     * 渲染表格
     */
    private function renderTable(array $component): string
    {
        $config = $component['config'];
        $id = $component['id'];
        
        $headers = $config['headers'] ?? [];
        $rows = $config['rows'] ?? [];
        
        $headerHtml = '';
        foreach ($headers as $header) {
            $headerHtml .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        
        $rowsHtml = '';
        foreach ($rows as $row) {
            $rowHtml = '';
            foreach ($row as $cell) {
                $rowHtml .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $rowsHtml .= '<tr>' . $rowHtml . '</tr>';
        }
        
        return '<table class="table" data-id="' . $id . '">
            <thead><tr>' . $headerHtml . '</tr></thead>
            <tbody>' . $rowsHtml . '</tbody>
        </table>';
    }

    /**
     * 构建样式
     */
    private function buildStyles(): string
    {
        return implode("\n", $this->styles);
    }

    /**
     * 构建脚本
     */
    private function buildScripts(): string
    {
        return implode("\n", $this->scripts);
    }

    /**
     * 初始化默认样式
     */
    private function initDefaultStyles(): void
    {
        $this->styles[] = '
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: Arial, sans-serif; background: #f5f5f5; }
            .libui-preview { padding: 20px; }
            
            .window { background: white; border: 1px solid #ccc; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .window-title { background: #e0e0e0; padding: 10px; font-weight: bold; border-bottom: 1px solid #ccc; }
            .window-content { padding: 15px; }
            
            .box-horizontal { display: flex; gap: 10px; }
            .box-vertical { display: flex; flex-direction: column; gap: 10px; }
            
            .button { background: #007cba; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
            .button:hover { background: #005a87; }
            .button:disabled { background: #ccc; cursor: not-allowed; }
            
            .label { padding: 5px 0; }
            .entry { border: 1px solid #ccc; padding: 6px; border-radius: 4px; }
            .checkbox { display: flex; align-items: center; gap: 5px; }
            
            .combobox { border: 1px solid #ccc; padding: 6px; border-radius: 4px; }
            
            .slider-container { display: flex; align-items: center; gap: 10px; }
            .slider { flex: 1; }
            .slider-value { min-width: 30px; text-align: center; }
            
            .spinbox-container { display: flex; align-items: center; }
            .spinbox { width: 60px; text-align: center; border: 1px solid #ccc; padding: 4px; }
            .spinbox-up, .spinbox-down { background: #eee; border: 1px solid #ccc; padding: 4px 8px; cursor: pointer; }
            
            .progressbar { background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px; height: 20px; position: relative; }
            .progressbar-fill { background: #007cba; height: 100%; transition: width 0.3s; }
            .progressbar-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 12px; }
            
            .separator { border: none; border-top: 1px solid #ccc; margin: 10px 0; }
            
            .group { border: 1px solid #ccc; border-radius: 4px; padding: 10px; }
            .group legend { padding: 0 10px; font-weight: bold; }
            
            .grid { display: grid; gap: 10px; }
            .grid-item { border: 1px solid #ddd; padding: 10px; border-radius: 4px; }
            
            .tab { border: 1px solid #ccc; border-radius: 4px; }
            .tab-header { display: flex; border-bottom: 1px solid #ccc; }
            .tab-button { background: none; border: none; padding: 10px 15px; cursor: pointer; border-bottom: 2px solid transparent; }
            .tab-button.active { border-bottom-color: #007cba; background: #f0f8ff; }
            .tab-body { padding: 15px; }
            .tab-content { display: none; }
            .tab-content.active { display: block; }
            
            .table { width: 100%; border-collapse: collapse; }
            .table th, .table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
            .table th { background: #f0f0f0; font-weight: bold; }
            
            .unknown { background: #ffe6e6; border: 1px solid #ff9999; padding: 10px; border-radius: 4px; color: #cc0000; }
        ';

        $this->scripts[] = '
            // Tab switching
            document.addEventListener("click", function(e) {
                if (e.target.classList.contains("tab-button")) {
                    const tabId = e.target.dataset.tab;
                    const container = e.target.closest(".tab");
                    
                    container.querySelectorAll(".tab-button").forEach(btn => btn.classList.remove("active"));
                    container.querySelectorAll(".tab-content").forEach(content => content.classList.remove("active"));
                    
                    e.target.classList.add("active");
                    container.querySelector(`[data-content="${tabId}"]`).classList.add("active");
                }
            });
            
            // Slider value update
            document.addEventListener("input", function(e) {
                if (e.target.classList.contains("slider")) {
                    const container = e.target.closest(".slider-container");
                    container.querySelector(".slider-value").textContent = e.target.value;
                }
            });
            
            // Spinbox controls
            document.addEventListener("click", function(e) {
                if (e.target.classList.contains("spinbox-up")) {
                    const input = e.target.previousElementSibling;
                    input.value = Math.min(parseInt(input.value) + 1, parseInt(input.max));
                } else if (e.target.classList.contains("spinbox-down")) {
                    const input = e.target.nextElementSibling;
                    input.value = Math.max(parseInt(input.value) - 1, parseInt(input.min));
                }
            });
        ';
    }
}
