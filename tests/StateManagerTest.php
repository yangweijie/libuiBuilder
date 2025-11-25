<?php

use PHPUnit\Framework\TestCase;
use Kingbes\Libui\Declarative\StateManager;

class StateManagerTest extends TestCase
{
    public function testCanSetAndGetSimpleValue(): void
    {
        StateManager::set('test', 'value');
        $this->assertEquals('value', StateManager::get('test'));
    }

    public function testReturnsDefaultValueWhenKeyDoesNotExist(): void
    {
        $this->assertEquals('default', StateManager::get('nonexistent', 'default'));
    }

    public function testSupportsDotNotationForNestedValues(): void
    {
        StateManager::set('form.username', 'testuser');
        $this->assertEquals('testuser', StateManager::get('form.username'));
    }

    public function testSupportsDeepNestingWithDotNotation(): void
    {
        StateManager::set('user.profile.name', 'John Doe');
        $this->assertEquals('John Doe', StateManager::get('user.profile.name'));
    }

    public function testCanHandleBooleanValuesInNestedStructure(): void
    {
        StateManager::set('form.agreeTerms', true);
        $this->assertTrue(StateManager::get('form.agreeTerms'));
        
        StateManager::set('form.agreeTerms', false);
        $this->assertFalse(StateManager::get('form.agreeTerms'));
    }

    public function testCanOverrideExistingNestedValues(): void
    {
        StateManager::set('form.username', 'first');
        $this->assertEquals('first', StateManager::get('form.username'));
        
        StateManager::set('form.username', 'second');
        $this->assertEquals('second', StateManager::get('form.username'));
    }

    public function testCanGetEntireNestedStructure(): void
    {
        StateManager::set('form.username', 'testuser');
        StateManager::set('form.email', 'test@example.com');
        
        $form = StateManager::get('form');
        $this->assertIsArray($form);
        $this->assertEquals('testuser', $form['username']);
        $this->assertEquals('test@example.com', $form['email']);
    }
}