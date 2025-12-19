<?php

use PHPUnit\Framework\TestCase;

test('basic functionality', function () {
    $sm = \Kingbes\Libui\View\State\StateManager::instance();
    $sm->set('test', 'value');
    expect($sm->get('test'))->toBe('value');
});

test('another test', function () {
    $ed = new \Kingbes\Libui\View\Core\Event\EventDispatcher();
    expect($ed)->toBeInstanceOf(\Kingbes\Libui\View\Core\Event\EventDispatcher::class);
});