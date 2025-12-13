<?php
/**
 * 响应式网格布局示例 - Builder API 模式
 * 
 * 演示内容：
 * - GridBuilder 的基本使用
 * - 12列网格系统的比例分配
 * - 不同屏幕尺寸下的自适应布局
 * - 组件在网格中的比例设置
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Components\BoxBuilder;
use Kingbes\Libui\View\Components\ButtonBuilder;
use Kingbes\Libui\View\Components\EntryBuilder;
use Kingbes\Libui\View\Components\LabelBuilder;
use Kingbes\Libui\View\Components\SeparatorBuilder;
use Kingbes\Libui\View\Components\WindowBuilder;
use Kingbes\Libui\View\Components\GridBuilder;

App::init();

$app = new WindowBuilder([
    'title' => '响应式网格布局 - Builder API',
    'width' => 800,
    'height' => 600
]);

// 主容器
$mainContainer = new BoxBuilder('vertical', ['padded' => true]);

// 创建单一网格，与HTML版本结构一致
$mainGrid = new GridBuilder(['padded' => true]);

// 创建所有组件并添加到网格
$components = [];

// Row 0: 标题
$title = new LabelBuilder(['text' => '12列响应式网格演示']);
$title->align('center');
$mainGrid->place($title, 0, 0, 1, 12);
$title->setConfig('row', 0);
$title->setConfig('col', 0);
$title->setConfig('rowspan', 1);
$title->setConfig('colspan', 12);
$components[] = $title;

// Row 1: 分隔线
$separator1 = new SeparatorBuilder();
$mainGrid->place($separator1, 1, 0, 1, 12);
$separator1->setConfig('row', 1);
$separator1->setConfig('col', 0);
$separator1->setConfig('rowspan', 1);
$separator1->setConfig('colspan', 12);
$components[] = $separator1;

// Row 2: 说明文本
$description = new LabelBuilder(['text' => '这是一个12列网格系统，组件按比例分配宽度']);
$mainGrid->place($description, 2, 0, 1, 12);
$description->setConfig('row', 2);
$description->setConfig('col', 0);
$description->setConfig('rowspan', 1);
$description->setConfig('colspan', 12);
$components[] = $description;

// Row 3: 第一组：6+6 布局
$leftLabel1 = new LabelBuilder(['text' => '左侧 - 6列']);
$rightLabel1 = new LabelBuilder(['text' => '右侧 - 6列']);
$mainGrid->place($leftLabel1, 3, 0, 1, 6);
$mainGrid->place($rightLabel1, 3, 6, 1, 6);
$leftLabel1->setConfig('row', 3);
$leftLabel1->setConfig('col', 0);
$leftLabel1->setConfig('rowspan', 1);
$leftLabel1->setConfig('colspan', 6);
$rightLabel1->setConfig('row', 3);
$rightLabel1->setConfig('col', 6);
$rightLabel1->setConfig('rowspan', 1);
$rightLabel1->setConfig('colspan', 6);
$components[] = $leftLabel1;
$components[] = $rightLabel1;

// Row 4: 第二组：4+4+4 布局
$leftLabel2 = new LabelBuilder(['text' => '左侧 - 4列']);
$middleLabel2 = new LabelBuilder(['text' => '中间 - 4列']);
$rightLabel2 = new LabelBuilder(['text' => '右侧 - 4列']);
$mainGrid->place($leftLabel2, 4, 0, 1, 4);
$mainGrid->place($middleLabel2, 4, 4, 1, 4);
$mainGrid->place($rightLabel2, 4, 8, 1, 4);
$leftLabel2->setConfig('row', 4);
$leftLabel2->setConfig('col', 0);
$leftLabel2->setConfig('rowspan', 1);
$leftLabel2->setConfig('colspan', 4);
$middleLabel2->setConfig('row', 4);
$middleLabel2->setConfig('col', 4);
$middleLabel2->setConfig('rowspan', 1);
$middleLabel2->setConfig('colspan', 4);
$rightLabel2->setConfig('row', 4);
$rightLabel2->setConfig('col', 8);
$rightLabel2->setConfig('rowspan', 1);
$rightLabel2->setConfig('colspan', 4);
$components[] = $leftLabel2;
$components[] = $middleLabel2;
$components[] = $rightLabel2;

// Row 5: 第三组：3+6+3 布局
$leftLabel3 = new LabelBuilder(['text' => '左侧 - 3列']);
$middleLabel3 = new LabelBuilder(['text' => '中间 - 6列']);
$rightLabel3 = new LabelBuilder(['text' => '右侧 - 3列']);
$mainGrid->place($leftLabel3, 5, 0, 1, 3);
$mainGrid->place($middleLabel3, 5, 3, 1, 6);
$mainGrid->place($rightLabel3, 5, 9, 1, 3);
$leftLabel3->setConfig('row', 5);
$leftLabel3->setConfig('col', 0);
$leftLabel3->setConfig('rowspan', 1);
$leftLabel3->setConfig('colspan', 3);
$middleLabel3->setConfig('row', 5);
$middleLabel3->setConfig('col', 3);
$middleLabel3->setConfig('rowspan', 1);
$middleLabel3->setConfig('colspan', 6);
$rightLabel3->setConfig('row', 5);
$rightLabel3->setConfig('col', 9);
$rightLabel3->setConfig('rowspan', 1);
$rightLabel3->setConfig('colspan', 3);
$components[] = $leftLabel3;
$components[] = $middleLabel3;
$components[] = $rightLabel3;

// Row 6: 第四组：2+3+4+3 布局
$label1 = new LabelBuilder(['text' => '2列']);
$label2 = new LabelBuilder(['text' => '3列']);
$label3 = new LabelBuilder(['text' => '4列']);
$label4 = new LabelBuilder(['text' => '3列']);
$mainGrid->place($label1, 6, 0, 1, 2);
$mainGrid->place($label2, 6, 2, 1, 3);
$mainGrid->place($label3, 6, 5, 1, 4);
$mainGrid->place($label4, 6, 9, 1, 3);
$label1->setConfig('row', 6);
$label1->setConfig('col', 0);
$label1->setConfig('rowspan', 1);
$label1->setConfig('colspan', 2);
$label2->setConfig('row', 6);
$label2->setConfig('col', 2);
$label2->setConfig('rowspan', 1);
$label2->setConfig('colspan', 3);
$label3->setConfig('row', 6);
$label3->setConfig('col', 5);
$label3->setConfig('rowspan', 1);
$label3->setConfig('colspan', 4);
$label4->setConfig('row', 6);
$label4->setConfig('col', 9);
$label4->setConfig('rowspan', 1);
$label4->setConfig('colspan', 3);
$components[] = $label1;
$components[] = $label2;
$components[] = $label3;
$components[] = $label4;

// Row 7: 第五组：按钮组 3+4+5 布局
$btn1 = new ButtonBuilder(['text' => '小按钮']);
$btn2 = new ButtonBuilder(['text' => '中等按钮']);
$btn3 = new ButtonBuilder(['text' => '大按钮']);
$mainGrid->place($btn1, 7, 0, 1, 3);
$mainGrid->place($btn2, 7, 3, 1, 4);
$mainGrid->place($btn3, 7, 7, 1, 5);
$btn1->setConfig('row', 7);
$btn1->setConfig('col', 0);
$btn1->setConfig('rowspan', 1);
$btn1->setConfig('colspan', 3);
$btn2->setConfig('row', 7);
$btn2->setConfig('col', 3);
$btn2->setConfig('rowspan', 1);
$btn2->setConfig('colspan', 4);
$btn3->setConfig('row', 7);
$btn3->setConfig('col', 7);
$btn3->setConfig('rowspan', 1);
$btn3->setConfig('colspan', 5);
$components[] = $btn1;
$components[] = $btn2;
$components[] = $btn3;

// Row 8: 分隔线
$separator2 = new SeparatorBuilder();
$mainGrid->place($separator2, 8, 0, 1, 12);
$separator2->setConfig('row', 8);
$separator2->setConfig('col', 0);
$separator2->setConfig('rowspan', 1);
$separator2->setConfig('colspan', 12);
$components[] = $separator2;

// Row 9-11: 输入控件演示
$nameLabel = new LabelBuilder(['text' => '姓名:']);
$nameInput = new EntryBuilder(['placeholder' => '请输入姓名']);
$emailLabel = new LabelBuilder(['text' => '邮箱:']);
$emailInput = new EntryBuilder(['placeholder' => '请输入邮箱']);
$passwordLabel = new LabelBuilder(['text' => '密码:']);
$passwordInput = new EntryBuilder(['placeholder' => '请输入密码']);

$mainGrid->place($nameLabel, 9, 0, 1, 3);
$mainGrid->place($nameInput, 9, 3, 1, 9);
$mainGrid->place($emailLabel, 10, 0, 1, 3);
$mainGrid->place($emailInput, 10, 3, 1, 9);
$mainGrid->place($passwordLabel, 11, 0, 1, 3);
$mainGrid->place($passwordInput, 11, 3, 1, 9);

$nameLabel->setConfig('row', 9);
$nameLabel->setConfig('col', 0);
$nameLabel->setConfig('rowspan', 1);
$nameLabel->setConfig('colspan', 3);
$nameInput->setConfig('row', 9);
$nameInput->setConfig('col', 3);
$nameInput->setConfig('rowspan', 1);
$nameInput->setConfig('colspan', 9);
$emailLabel->setConfig('row', 10);
$emailLabel->setConfig('col', 0);
$emailLabel->setConfig('rowspan', 1);
$emailLabel->setConfig('colspan', 3);
$emailInput->setConfig('row', 10);
$emailInput->setConfig('col', 3);
$emailInput->setConfig('rowspan', 1);
$emailInput->setConfig('colspan', 9);
$passwordLabel->setConfig('row', 11);
$passwordLabel->setConfig('col', 0);
$passwordLabel->setConfig('rowspan', 1);
$passwordLabel->setConfig('colspan', 3);
$passwordInput->setConfig('row', 11);
$passwordInput->setConfig('col', 3);
$passwordInput->setConfig('rowspan', 1);
$passwordInput->setConfig('colspan', 9);

$components[] = $nameLabel;
$components[] = $nameInput;
$components[] = $emailLabel;
$components[] = $emailInput;
$components[] = $passwordLabel;
$components[] = $passwordInput;

// Row 12: 分隔线
$separator3 = new SeparatorBuilder();
$mainGrid->place($separator3, 12, 0, 1, 12);
$separator3->setConfig('row', 12);
$separator3->setConfig('col', 0);
$separator3->setConfig('rowspan', 1);
$separator3->setConfig('colspan', 12);
$components[] = $separator3;

// Row 13: 按钮组标签
$buttonLabel = new LabelBuilder(['text' => '按钮组演示:']);
$mainGrid->place($buttonLabel, 13, 0, 1, 12);
$buttonLabel->setConfig('row', 13);
$buttonLabel->setConfig('col', 0);
$buttonLabel->setConfig('rowspan', 1);
$buttonLabel->setConfig('colspan', 12);
$components[] = $buttonLabel;

// Row 14: 底部按钮组
$saveBtn = new ButtonBuilder(['text' => '保存']);
$cancelBtn = new ButtonBuilder(['text' => '取消']);
$quitBtn = new ButtonBuilder(['text' => '退出']);

$saveBtn->onClick(function() {
    echo "保存操作执行\n";
});

$cancelBtn->onClick(function() {
    echo "取消操作执行\n";
});

$quitBtn->onClick(function($button) {
    App::quit();
});

$mainGrid->place($saveBtn, 14, 0, 1, 4);
$mainGrid->place($cancelBtn, 14, 4, 1, 4);
$mainGrid->place($quitBtn, 14, 8, 1, 4);

$saveBtn->setConfig('row', 14);
$saveBtn->setConfig('col', 0);
$saveBtn->setConfig('rowspan', 1);
$saveBtn->setConfig('colspan', 4);
$cancelBtn->setConfig('row', 14);
$cancelBtn->setConfig('col', 4);
$cancelBtn->setConfig('rowspan', 1);
$cancelBtn->setConfig('colspan', 4);
$quitBtn->setConfig('row', 14);
$quitBtn->setConfig('col', 8);
$quitBtn->setConfig('rowspan', 1);
$quitBtn->setConfig('colspan', 4);

$components[] = $saveBtn;
$components[] = $cancelBtn;
$components[] = $quitBtn;

// 将所有组件添加到网格的children数组中
//$mainGrid->contains($components);
$mainGrid->build();
// 添加到主容器
$mainContainer->contains([$mainGrid]);

$app->contains([$mainContainer]);
$app->show();