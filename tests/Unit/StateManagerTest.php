<?php

test('StateManager basic functionality', function () {
    $sm = \Kingbes\Libui\View\State\StateManager::instance();
    
    $sm->set('test_key', 'test_value');
    expect($sm->get('test_key'))->toBe('test_value');
    
    $sm->reset();
    expect($sm->get('test_key'))->toBeNull();
});

test('StateManager singleton', function () {
    $sm1 = \Kingbes\Libui\View\State\StateManager::instance();
    $sm2 = \Kingbes\Libui\View\State\StateManager::instance();
    
    expect($sm1)->toBe($sm2);
});