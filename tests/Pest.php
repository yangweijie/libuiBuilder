<?php

// 全局测试设置
beforeEach(function () {
    date_default_timezone_set('Asia/Shanghai');
    
    // 设置错误报告
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
});