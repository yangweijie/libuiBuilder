<?php

use PHPUnit\Framework\TestCase;
use Kingbes\Libui\Declarative\StateManager;

class CheckboxComponentTest extends TestCase
{
    public function testCheckboxComponentHandlesVModel(): void
    {
        // 测试 StateManager 可以正确处理 checkbox 的 v-model 绑定
        StateManager::set('form.agreeTerms', false);
        
        // 模拟 checkbox 切换操作
        StateManager::set('form.agreeTerms', true);
        
        $this->assertTrue(StateManager::get('form.agreeTerms'));
        
        StateManager::set('form.agreeTerms', false);
        $this->assertFalse(StateManager::get('form.agreeTerms'));
    }

    public function testNestedCheckboxState(): void
    {
        // 测试多层嵌套状态
        StateManager::set('user.preferences.notifications', true);
        StateManager::set('user.preferences.theme', 'dark');
        
        $this->assertTrue(StateManager::get('user.preferences.notifications'));
        $this->assertEquals('dark', StateManager::get('user.preferences.theme'));
    }
}