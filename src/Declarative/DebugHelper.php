<?php

namespace Kingbes\Libui\Declarative;

// 添加调试工具
use Kingbes\Libui\Declarative\Components\Component;

class DebugHelper
{
    public static function dumpComponentState(Component $component): void
    {
        $state = $component->exportState();
        echo "=== Component State ===\n";
        echo "Tag: {$state['tag']}\n";
        echo "Ref: {$state['ref']}\n";
        echo "Value: " . print_r($state['value'], true) . "\n";
        echo "Events: " . implode(', ', $state['events']) . "\n";
        echo "========================\n";
    }

    public static function testEventExpression(Component $component, string $expression): void
    {
        echo "=== Testing Event Expression ===\n";
        echo "Expression: {$expression}\n";

        $parser = $component;
        if (method_exists($parser, 'parseEventHandler')) {
            try {
                $handler = $parser->parseEventHandler($expression);
                $result = $handler();
                echo "Result: " . print_r($result, true) . "\n";
            } catch (\Throwable $e) {
                echo "Error: {$e->getMessage()}\n";
            }
        }
        echo "================================\n";
    }
}