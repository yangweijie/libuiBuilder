<?php

namespace Kingbes\Libui\Declarative\Components;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use Kingbes\Libui\Declarative\ComponentRegistry;
use Kingbes\Libui\Declarative\StateManager;

class TemplateParser
{
    public function parse(string $template): Component
    {
        // 解析XML/HTML风格的模板
        // Preprocess the template to handle namespace prefixes and invalid attribute names
        $template = $this->replaceNamespacePrefixes($template);
        $template = $this->normalizeAttributeNames($template);
        $template = $this->processDynamicAttributes($template);

        $dom = new DOMDocument();
        libxml_use_internal_errors(true); // Suppress XML warnings
        $dom->loadXML($template);
        $element = $dom->documentElement;
        libxml_clear_errors();

        if ($element === null) {
            throw new InvalidArgumentException("Invalid template: could not parse as XML");
        }

        return $this->parseElement($element);
    }

    private function parseElement(DOMElement $element)
    {
        $tagName = $element->tagName;
        // Handle namespace prefixes by extracting just the local name (after the colon)
        if (str_contains($tagName, ':')) {
            $tagName = substr($tagName, strpos($tagName, ':') + 1);
        }
        $componentClass = ComponentRegistry::resolve($tagName);

        if (!$componentClass) {
            throw new InvalidArgumentException("Unknown component: {$tagName}");
        }

        // 提取属性
        $attributes = [];
        foreach ($element->attributes as $attr) {
            $attributes[$attr->name] = $attr->value;
        }

        // Process conditional rendering attributes
        if (isset($attributes['v-show'])) {
            $showCondition = $attributes['v-show'];
            $shouldShow = $this->evaluateCondition($showCondition);
            if (!$shouldShow) {
                // For now, we'll set display:none if condition is false
                $currentStyle = $attributes['style'] ?? '';
                $attributes['style'] = $currentStyle . ' display: none;';
            }
            unset($attributes['v-show']);
        }

        // Process dynamic attributes (those starting with :)
        $attributes = $this->processDynamicAttributeValues($attributes);

        // Restore original attribute names from normalized ones BEFORE creating the component
        // This is critical because Component constructor calls parseEventBindings() immediately
        $attributes = $this->restoreAttributeNames($attributes);

        $component = new $componentClass($attributes);

        // 递归解析子元素
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $component->addChild($this->parseElement($child));
            }
        }

        return $component;
    }

    private function normalizeAttributeNames(string $template): string
    {
        // Normalize invalid XML attribute names to make them valid for parsing
        // Replace @event attributes with data-event-* equivalents
        $template = preg_replace('/@([a-zA-Z0-9_-]+)=/', 'data-event-$1=', $template);
        // Replace v-* binding attributes with data-binding-* equivalents
        $template = preg_replace('/v-([a-zA-Z0-9_-]+)=/', 'data-binding-$1=', $template);
        // Replace :dynamic attributes with data-dynamic-* equivalents
        $template = preg_replace('/:([a-zA-Z0-9_-]+)=/', 'data-dynamic-$1=', $template);
        // Replace other special attributes as needed
        return $template;
    }

    private function processDynamicAttributes(string $template): string
    {
        // Process :attribute="expression" syntax by evaluating the expressions first
        // This is a simplified version - in a real implementation, you'd want to parse more carefully
        return $template;
    }

    private function processDynamicAttributeValues(array $attributes): array
    {
        $processed = [];
        foreach ($attributes as $key => $value) {
            if (str_starts_with($key, 'data-dynamic-')) {
                // Process dynamic attribute values like :disabled="!formValid"
                $attrName = substr($key, 15); // remove 'data-dynamic-' prefix
                $processed[$attrName] = $this->evaluateExpression($value);
            } else {
                $processed[$key] = $value;
            }
        }

        return $processed;
    }

    private function evaluateExpression(string $expression): string
    {
        // Simple expression evaluation for state references
        // Replace getState('key') calls with actual values
        $pattern = '/getState\(\'([^\']+)\'(?:,\s*\'([^\']*)\')?\)/';
        $result = preg_replace_callback($pattern, function ($matches) {
            $key = $matches[1];
            $default = $matches[2] ?? '';
            $value = \Kingbes\Libui\Declarative\StateManager::get($key, $default);
            // 确保返回字符串，如果值是数组则转换为字符串
            return is_array($value) ? json_encode($value) : (string)$value;
        }, $expression);

        return $result;
    }

    private function evaluateCondition(string $condition): bool
    {
        // Simple condition evaluation for basic cases
        // Replace state references with actual values first
        $evaluated = $this->evaluateExpression($condition);
        
        // Handle simple comparison expressions like "getState('key') === 'value'"
        // This is a very basic implementation - a real system would need a proper parser
        if (strpos($evaluated, '===') !== false) {
            $parts = explode('===', $evaluated);
            if (count($parts) === 2) {
                $left = trim($parts[0]);
                $right = trim($parts[1]);
                // Remove quotes if present
                $right = trim($right, '"\' ');
                return $left === $right;
            }
        } elseif (strpos($evaluated, '==') !== false) {
            $parts = explode('==', $evaluated);
            if (count($parts) === 2) {
                $left = trim($parts[0]);
                $right = trim($parts[1]);
                $right = trim($right, '"\' ');
                return $left == $right;
            }
        } elseif (strpos($evaluated, '!=') !== false) {
            $parts = explode('!=', $evaluated);
            if (count($parts) === 2) {
                $left = trim($parts[0]);
                $right = trim($parts[1]);
                $right = trim($right, '"\' ');
                return $left != $right;
            }
        } elseif (strpos($evaluated, '!') === 0) {
            // Handle negation like "!formValid", "!getState('key')"
            $value = trim(substr($evaluated, 1));
            // If it's a function call, evaluate it first
            if (strpos($value, 'getState') === 0) {
                $extractedValue = $this->evaluateExpression($value);
                return !$extractedValue;
            }
            return !$value;
        }
        
        // For direct values, convert to boolean
        return (bool)$evaluated;
    }

    private function replaceNamespacePrefixes(string $template): string
    {
        // Replace namespace prefixes like 'ui:window' with just 'window'
        // This handles both opening and closing tags properly
        // For patterns like 'ui:window' -> '<window' and '</ui:window>' -> '</window>'
        return preg_replace('/(<\/?)([a-zA-Z0-9_]+:)([a-zA-Z0-9_]+)/', '$1$3', $template);
    }

    private function restoreAttributeNames(array $attributes): array
    {
        // Restore original attribute names from normalized ones
        $restored = [];

        foreach ($attributes as $attrName => $attrValue) {
            if (str_starts_with($attrName, 'data-event-')) {
                // data-event-click -> @click
                $restored['@' . substr($attrName, 11)] = $attrValue;
            } elseif (str_starts_with($attrName, 'data-binding-')) {
                // data-binding-model -> v-model
                $restored['v-' . substr($attrName, 13)] = $attrValue;
            } elseif (str_starts_with($attrName, 'data-dynamic-')) {
                // data-dynamic-disabled -> :disabled
                $restored[':' . substr($attrName, 15)] = $attrValue;
            } else {
                $restored[$attrName] = $attrValue;
            }
        }

        return $restored;
    }
}