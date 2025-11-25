<?php

use PHPUnit\Framework\TestCase;
use Kingbes\Libui\Declarative\StateManager;
use Kingbes\Libui\Declarative\Components\TemplateParser;

class TemplateParserTest extends TestCase
{
    public function testEvaluateExpressionWithGetState(): void
    {
        // 设置测试数据
        StateManager::set('form.username', 'testuser');
        StateManager::set('test.value', 'testvalue');
        
        $parser = new TemplateParser();
        $reflection = new \ReflectionClass($parser);
        $method = $reflection->getMethod('evaluateExpression');
        $method->setAccessible(true);
        
        $result = $method->invoke($parser, "getState('form.username', 'default')");
        $this->assertEquals('testuser', $result);
        
        $result2 = $method->invoke($parser, "getState('test.value', 'default')");
        $this->assertEquals('testvalue', $result2);
        
        $result3 = $method->invoke($parser, "getState('nonexistent', 'default')");
        $this->assertEquals('default', $result3);
    }

    public function testEvaluateComplexExpressions(): void
    {
        StateManager::set('user.name', 'John');
        StateManager::set('user.age', 25);
        
        $parser = new TemplateParser();
        $reflection = new \ReflectionClass($parser);
        $method = $reflection->getMethod('evaluateExpression');
        $method->setAccessible(true);
        
        $expression = "'User: ' . getState('user.name', 'Anonymous') . ', Age: ' . getState('user.age', 0)";
        $result = $method->invoke($parser, $expression);
        $this->assertEquals('User: John, Age: 25', $result);
    }

    public function testGetStateNestedFunction(): void
    {
        StateManager::set('app.config.debug', true);
        StateManager::set('app.version', '1.0.0');
        
        $this->assertTrue(StateManager::get('app.config.debug'));
        $this->assertEquals('1.0.0', StateManager::get('app.version'));
    }
}