<?php

use PHPUnit\Framework\TestCase;
use Kingbes\Libui\Declarative\StateManager;

class TemplateParserTest extends TestCase
{
    public function testGetStateNestedFunction(): void
    {
        StateManager::set('app.config.debug', true);
        StateManager::set('app.version', '1.0.0');
        
        $this->assertTrue(StateManager::get('app.config.debug'));
        $this->assertEquals('1.0.0', StateManager::get('app.version'));
    }
    
    public function testComplexNestedAccess(): void
    {
        // 测试复杂嵌套
        StateManager::set('user.profile.personal.name', 'John Doe');
        StateManager::set('user.profile.personal.email', 'john@example.com');
        StateManager::set('user.profile.settings.theme', 'dark');
        StateManager::set('user.profile.settings.language', 'en');
        
        $this->assertEquals('John Doe', StateManager::get('user.profile.personal.name'));
        $this->assertEquals('john@example.com', StateManager::get('user.profile.personal.email'));
        $this->assertEquals('dark', StateManager::get('user.profile.settings.theme'));
        $this->assertEquals('en', StateManager::get('user.profile.settings.language'));
    }
}