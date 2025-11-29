<?php

require_once __DIR__ . '/vendor/autoload.php';

use Kingbes\Libui\View\State\ComponentRef;
use Kingbes\Libui\View\State\StateManager;
use Kingbes\Libui\View\ComponentBuilder;

echo "开始 ComponentRef 测试...\n";

// 测试 1: ComponentRef 基本功能
echo "测试 1: ComponentRef 基本功能\n";
$mockComponent = new class extends ComponentBuilder {
    private $value = 'test';
    
    public function getValue() {
        return $this->value;
    }
    
    public function setValue($value): void {
        $this->value = $value;
    }
    
    public function getConfig(string $key, $default = null) {
        return $key ? 'config_value' : ['key' => 'config_value'];
    }
};

$componentRef = new ComponentRef('test-component', $mockComponent);
echo "✓ ComponentRef 创建成功\n";
echo "✓ ID: " . $componentRef->getId() . "\n";
echo "✓ 值: " . $componentRef->getValue() . "\n";

// 测试 2: StateManager 集成
echo "\n测试 2: StateManager 集成\n";
$stateManager = StateManager::instance();
$stateManager->registerComponent('test-component', $componentRef);
$retrievedRef = $stateManager->getComponent('test-component');
echo "✓ 组件注册和获取成功\n";
echo "✓ 获取的组件ID: " . $retrievedRef->getId() . "\n";

// 测试 3: 值设置和获取
echo "\n测试 3: 值设置和获取\n";
$componentRef->setValue('new value');
echo "✓ 设置新值: " . $componentRef->getValue() . "\n";

// 测试 4: 配置获取
echo "\n测试 4: 配置获取\n";
$config = $componentRef->getConfig();
echo "✓ 获取完整配置\n";
$specificConfig = $componentRef->getConfig('key', null);
echo "✓ 获取特定配置: " . $specificConfig . "\n";

echo "\n所有 ComponentRef 测试完成！\n";
