<?php

use Kingbes\Libui\Declarative\StateManager;

it('can set and get a simple value', function () {
    StateManager::set('test', 'value');
    expect(StateManager::get('test'))->toBe('value');
});

it('returns default value when key does not exist', function () {
    expect(StateManager::get('nonexistent', 'default'))->toBe('default');
});

it('supports dot notation for nested values', function () {
    StateManager::set('form.username', 'testuser');
    expect(StateManager::get('form.username'))->toBe('testuser');
});

it('supports deep nesting with dot notation', function () {
    StateManager::set('user.profile.name', 'John Doe');
    expect(StateManager::get('user.profile.name'))->toBe('John Doe');
});

it('can handle boolean values in nested structure', function () {
    StateManager::set('form.agreeTerms', true);
    expect(StateManager::get('form.agreeTerms'))->toBeTrue();
    
    StateManager::set('form.agreeTerms', false);
    expect(StateManager::get('form.agreeTerms'))->toBeFalse();
});

it('can override existing nested values', function () {
    StateManager::set('form.username', 'first');
    expect(StateManager::get('form.username'))->toBe('first');
    
    StateManager::set('form.username', 'second');
    expect(StateManager::get('form.username'))->toBe('second');
});

it('can get entire nested structure', function () {
    StateManager::set('form.username', 'testuser');
    StateManager::set('form.email', 'test@example.com');
    
    $form = StateManager::get('form');
    expect($form)->toBeArray()
         ->toHaveKey('username', 'testuser')
         ->toHaveKey('email', 'test@example.com');
});