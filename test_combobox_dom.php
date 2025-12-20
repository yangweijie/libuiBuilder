<?php

require_once __DIR__ . '/vendor/autoload.php';

use Kingbes\Libui\View\Builder\Builder;
use DOMDocument;
use DOMElement;

echo "=== Combobox 选项修复测试 ===\n";

// 测试1: 直接创建 combobox
echo "\n测试1: 直接创建 combobox\n";
$combobox1 = Builder::combobox()
    ->items(['选项1', '选项2', '选项3']);

echo "Combobox1 创建成功，选项: " . json_encode(['选项1', '选项2', 'options3']) . "\n";

// 测试2: 模拟 HTML 解析
echo "\n测试2: 模拟 HTML 解析\n";
$dom = new DOMDocument();
$dom->loadHTML('<combobox><option>选项1</option><option>选项2</option><option>选项3</option></combobox>');
$comboboxElement = $dom->getElementsByTagName('combobox')->item(0);

$options = [];
foreach ($comboboxElement->childNodes as $child) {
    if ($child instanceof DOMElement && strtolower($child->nodeName) === 'option') {
        $optionText = trim($child->textContent);
        $optionValue = $child->getAttribute('value');
        $optionValue = $optionValue !== null && $optionValue !== '' ? $optionValue : $optionText;
        $options[] = $optionValue;
        echo "DOM 解析选项: '{$optionValue}'\n";
    }
}

echo "DOM 解析结果: " . json_encode($options) . "\n";

echo "\n=== 测试完成 ===\n";
