<?php
// 检查当前环境是否可以使用 libuiBuilder
echo "检查 PHP 环境...\n";

// 检查 FFI 扩展
if (!extension_loaded('FFI')) {
    echo "错误: FFI 扩展未安装或未启用\n";
    exit(1);
} else {
    echo "✓ FFI 扩展已启用\n";
}

// 尝试加载 autoloader
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    echo "警告: vendor/autoload.php 不存在，可能需要运行 composer install\n";
    echo "请在项目根目录执行: composer install\n";
    exit(1);
} else {
    require_once $autoloadPath;
    echo "✓ Composer autoloader 加载成功\n";
}

// 检查 Builder 类
if (!class_exists('Kingbes\\Libui\\View\\Builder')) {
    echo "错误: Kingbes\\Libui\\View\\Builder 类不存在\n";
    echo "可能的原因:\n";
    echo "1. kingbes/libui 包未安装\n";
    echo "2. Composer autoloader 未正确生成\n";
    exit(1);
} else {
    echo "✓ Builder 类存在\n";
}

echo "\n环境检查完成！\n";
echo "如果要运行 GUI 应用，还需要确保:\n";
echo "1. kingbes/libui PHP 扩展已正确安装\n";
echo "2. 对应的 libui 库文件存在于系统中\n";