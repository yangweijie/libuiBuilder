<?php

namespace Kingbes\Libui\Declarative\Components;

use FFI\CData;
use Kingbes\Libui\Label;
use Kingbes\Libui\Declarative\StateManager;

class LabelComponent extends Component
{
    private $originalTextExpression;

    public function getTagName(): string
    {
        return 'ui:label';
    }

    public function render(): CData
    {
        $text = $this->getAttribute('text', '');
        $this->originalTextExpression = $text; // 保存原始表达式

        // 如果是动态表达式（包含getState等），我们需要监听状态变化
        $label = Label::create($text);

        // 如果原始文本包含表达式（如包含getState），则监听状态变化
        if ($this->originalTextExpression && strpos($this->originalTextExpression, 'getState') !== false) {
            $this->setupDynamicTextUpdate();
        }

        $this->handle = $label;
        return $label;
    }

    private function setupDynamicTextUpdate(): void
    {
        // 提取表达式中的状态键
        $pattern = '/getState\\(\'([^\']+)\'(?:,\s*\'([^\']*)\')?\)/';
        $matches = [];
        preg_match_all($pattern, $this->originalTextExpression, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $key) {
                // 监听状态变化并更新标签文本
                StateManager::watch($key, function($newValue) {
                    $this->updateText();
                });
            }
        }

        // 如果是嵌套属性（如 form.city），还需要监听父对象的变化
        $pattern = '/getState\\(\'([a-z0-9_]+\.[a-z0-9_]+)\'/i';
        $matches = [];
        preg_match_all($pattern, $this->originalTextExpression, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $key) {
                $parts = explode('.', $key);
                $parentKey = implode('.', array_slice($parts, 0, -1)); // 获取父键，如 'form'
                $childKey = end($parts); // 获取子键，如 'city'

                // 监听父对象的变化
                StateManager::watch($parentKey, function($newValue) use ($childKey) {
                    if (is_array($newValue) && isset($newValue[$childKey])) {
                        $this->updateText();
                    } else {
                        $this->updateText();
                    }
                });
            }
        }
    }

    private function updateText(): void
    {
        if (!$this->handle || !$this->originalTextExpression) {
            return;
        }

        // 重新计算动态文本
        $newText = $this->evaluateTextExpression($this->originalTextExpression);
        Label::setText($this->handle, $newText);
    }

    private function evaluateTextExpression(string $expression): string
    {
        // 简单的表达式评估，处理 getState 调用
        $pattern = '/getState\\(\'([^\']+)\'(?:,\s*\'([^\']*)\')?\)/';
        $result = preg_replace_callback($pattern, function ($matches) {
            $key = $matches[1];
            $default = $matches[2] ?? '';
            $value = \Kingbes\Libui\Declarative\StateManager::get($key, $default);
            return $value !== null ? $value : $default;
        }, $expression);

        // 如果表达式包含字符串连接操作（如 '选中的城市: ' . getState(...)），简单处理
        if (strpos($result, ' . ') !== false) {
            $parts = explode(' . ', $result);
            $evaluatedParts = [];
            foreach ($parts as $part) {
                $part = trim($part, " '\"");
                if (preg_match($pattern, $part)) {
                    // 如果部分包含 getState，重新计算
                    $evaluatedParts[] = $this->evaluateTextExpression($part);
                } else {
                    // 否则是静态文本
                    $evaluatedParts[] = $part;
                }
            }
            return implode(' ', $evaluatedParts); // 简单连接
        }

        return $result;
    }

    public function getValue()
    {
        return Label::text($this->getHandle());
    }

    public function setValue($value): void
    {
        Label::setText($this->getHandle(), $value);
        // 如果设置了新值，可能需要更新原始表达式
        if (is_string($value) && strpos($value, 'getState') !== false) {
            $this->originalTextExpression = $value;
            $this->setupDynamicTextUpdate();
        }
    }
}