<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\Builder\Builder;

/**
 * Base test case for all tests
 */
class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Reset global state before each test
        StateManager::reset();
        Builder::setStateManager(null);
        Builder::setEventDispatcher(null);
        Builder::setConfigManager(null);
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        StateManager::reset();
        Builder::setStateManager(null);
        Builder::setEventDispatcher(null);
        Builder::setConfigManager(null);
    }
}
