<?php

use Pest\TestSuite;

TestSuite::getInstance()
    ->use(['tests', 'src'])
    ->testsPath('tests')
    ->sourcePath('src');