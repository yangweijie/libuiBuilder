<?php
echo "环境检测测试\n";

function isHeadlessEnvironment(): bool {
    $headlessEnv = [
        'SSH_CONNECTION',
        'SSH_TTY', 
        'TERM',
        'DISPLAY'
    ];
    
    foreach ($headlessEnv as $env) {
        if (!getenv($env)) {
            return true;
        }
    }
    
    return false;
}

function isGuiSupported(): bool {
    if (PHP_OS_FAMILY === 'Linux') {
        return !empty(getenv('DISPLAY'));
    } elseif (PHP_OS_FAMILY === 'Darwin') {
        return true;
    } elseif (PHP_OS_FAMILY === 'Windows') {
        return true;
    }
    return false;
}

echo "操作系统: " . PHP_OS_FAMILY . "\n";
echo "无头环境: " . (isHeadlessEnvironment() ? '是' : '否') . "\n";
echo "GUI支持: " . (isGuiSupported() ? '是' : '否') . "\n";
echo "SSH模式: " . (getenv('SSH_CONNECTION') ? '是' : '否') . "\n";

try {
    echo "\n测试autoload...\n";
    require_once __DIR__ . '/../../vendor/autoload.php';
    echo "autoload成功\n";
    
    echo "测试App初始化...\n";
    \Kingbes\Libui\App::init();
    echo "App初始化成功\n";
    
    echo "测试完成\n";
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "致命错误: " . $e->getMessage() . "\n";
}