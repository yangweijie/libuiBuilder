<?php

use Pest\TestSuite;

TestSuite::getInstance()
    ->testsPath('tests')
    ->sourcePath('src');

// Global setup
beforeEach(function () {
    // 设置时区
    date_default_timezone_set('Asia/Shanghai');
    
    // 设置错误报告
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
});