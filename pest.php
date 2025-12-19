<?php

use Pest\TestSuite;
use Tests\TestCase;

TestSuite::getInstance()
    ->use(['tests', 'src'])
    ->testsPath('tests')
    ->sourcePath('src');

// Set default test case
TestSuite::getInstance()->beforeEach(function () {
    // Reset global state
    \Kingbes\Libui\View\State\StateManager::reset();
    \Kingbes\Libui\View\Builder\Builder::setStateManager(null);
    \Kingbes\Libui\View\Builder\Builder::setEventDispatcher(null);
    \Kingbes\Libui\View\Builder\Builder::setConfigManager(null);
});