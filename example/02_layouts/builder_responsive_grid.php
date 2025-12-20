<?php
/**
 * 响应式网格布局示例 - Builder API 模式
 * 
 * 演示内容：
 * - GridBuilder 的基本使用
 * - 12列网格系统的比例分配
 * - 不同屏幕尺寸下的自适应布局
 * - 组件在网格中的比例设置
 * - 统一 Builder API 方法使用
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder\Builder;

App::init();

$app = Builder::window()
    ->title('响应式网格布局 - Builder API')
    ->size(800, 600)
    ->margined(true);

// 主容器
$mainContainer = Builder::vbox()->padded(true);

// 创建单一网格，与HTML版本结构一致
$mainGrid = Builder::grid()->padded(true);

// Row 0: 标题
$title = Builder::label()
    ->id('title')
    ->text('12列响应式网格演示')
    ->align('center');
$mainGrid->place($title, 0, 0, 1, 12);

// Row 1: 分隔线
$separator1 = Builder::separator();
$mainGrid->place($separator1, 1, 0, 1, 12);

// Row 2: 说明文本
$description = Builder::label()
    ->id('description')
    ->text('这是一个12列网格系统，组件按比例分配宽度');
$mainGrid->place($description, 2, 0, 1, 12);

// Row 3: 第一组：6+6 布局
$leftLabel1 = Builder::label()
    ->id('leftLabel1')
    ->text('左侧 - 6列');
$rightLabel1 = Builder::label()
    ->id('rightLabel1')
    ->text('右侧 - 6列');
$mainGrid->place($leftLabel1, 3, 0, 1, 6);
$mainGrid->place($rightLabel1, 3, 6, 1, 6);

// Row 4: 第二组：4+4+4 布局
$leftLabel2 = Builder::label()
    ->id('leftLabel2')
    ->text('左侧 - 4列');
$middleLabel2 = Builder::label()
    ->id('middleLabel2')
    ->text('中间 - 4列');
$rightLabel2 = Builder::label()
    ->id('rightLabel2')
    ->text('右侧 - 4列');
$mainGrid->place($leftLabel2, 4, 0, 1, 4);
$mainGrid->place($middleLabel2, 4, 4, 1, 4);
$mainGrid->place($rightLabel2, 4, 8, 1, 4);

// Row 5: 第三组：3+6+3 布局
$leftLabel3 = Builder::label()
    ->id('leftLabel3')
    ->text('左侧 - 3列');
$middleLabel3 = Builder::label()
    ->id('middleLabel3')
    ->text('中间 - 6列');
$rightLabel3 = Builder::label()
    ->id('rightLabel3')
    ->text('右侧 - 3列');
$mainGrid->place($leftLabel3, 5, 0, 1, 3);
$mainGrid->place($middleLabel3, 5, 3, 1, 6);
$mainGrid->place($rightLabel3, 5, 9, 1, 3);

// Row 6: 第四组：2+3+4+3 布局
$label1 = Builder::label()
    ->id('label1')
    ->text('2列');
$label2 = Builder::label()
    ->id('label2')
    ->text('3列');
$label3 = Builder::label()
    ->id('label3')
    ->text('4列');
$label4 = Builder::label()
    ->id('label4')
    ->text('3列');
$mainGrid->place($label1, 6, 0, 1, 2);
$mainGrid->place($label2, 6, 2, 1, 3);
$mainGrid->place($label3, 6, 5, 1, 4);
$mainGrid->place($label4, 6, 9, 1, 3);

// Row 7: 第五组：按钮组 3+4+5 布局
$btn1 = Builder::button()
    ->id('btn1')
    ->text('小按钮');
$btn2 = Builder::button()
    ->id('btn2')
    ->text('中等按钮');
$btn3 = Builder::button()
    ->id('btn3')
    ->text('大按钮');
$mainGrid->place($btn1, 7, 0, 1, 3);
$mainGrid->place($btn2, 7, 3, 1, 4);
$mainGrid->place($btn3, 7, 7, 1, 5);

// Row 8: 分隔线
$separator2 = Builder::separator();
$mainGrid->place($separator2, 8, 0, 1, 12);

// Row 9-11: 输入控件演示
$nameLabel = Builder::label()
    ->id('nameLabel')
    ->text('姓名:');
$nameInput = Builder::entry()
    ->id('nameInput')
    ->placeholder('请输入姓名');
$emailLabel = Builder::label()
    ->id('emailLabel')
    ->text('邮箱:');
$emailInput = Builder::entry()
    ->id('emailInput')
    ->placeholder('请输入邮箱');
$passwordLabel = Builder::label()
    ->id('passwordLabel')
    ->text('密码:');
$passwordInput = Builder::entry()
    ->id('passwordInput')
    ->placeholder('请输入密码');

$mainGrid->place($nameLabel, 9, 0, 1, 3);
$mainGrid->place($nameInput, 9, 3, 1, 9);
$mainGrid->place($emailLabel, 10, 0, 1, 3);
$mainGrid->place($emailInput, 10, 3, 1, 9);
$mainGrid->place($passwordLabel, 11, 0, 1, 3);
$mainGrid->place($passwordInput, 11, 3, 1, 9);

// Row 12: 分隔线
$separator3 = Builder::separator();
$mainGrid->place($separator3, 12, 0, 1, 12);

// Row 13: 按钮组标签
$buttonLabel = Builder::label()
    ->id('buttonLabel')
    ->text('按钮组演示:');
$mainGrid->place($buttonLabel, 13, 0, 1, 12);

// Row 14: 底部按钮组
$saveBtn = Builder::button()
    ->id('saveBtn')
    ->text('保存')
    ->onClick(function() {
        echo "保存操作执行\n";
    });

$cancelBtn = Builder::button()
    ->id('cancelBtn')
    ->text('取消')
    ->onClick(function() {
        echo "取消操作执行\n";
    });

$quitBtn = Builder::button()
    ->id('quitBtn')
    ->text('退出')
    ->onClick(function($button) {
        App::quit();
    });

$mainGrid->place($saveBtn, 14, 0, 1, 4);
$mainGrid->place($cancelBtn, 14, 4, 1, 4);
$mainGrid->place($quitBtn, 14, 8, 1, 4);

// 添加到主容器
$mainContainer->contains([$mainGrid]);

$app->contains([$mainContainer]);
$app->show();