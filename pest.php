<?php

use Pest\Plugins\PHPUnit;

/*
|--------------------------------------------------------------------------
| Test Configuration
|--------------------------------------------------------------------------
|
| This file contains the configuration for your tests.
| You can customize your test suite here.
|
*/

 PHPUnit::handle()
    ->showOutput()
    ->toBeStrictAboutTestsThatDoNotTestAnything()
    ->toBeStrictAboutOutputDuringTests()
    ->toBeStrictAboutTodoAnnotatedTests()
    ->toBeStrictAboutTestSize();