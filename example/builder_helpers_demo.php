<?php
/**
 * Builder 链式辅助函数使用示例
 * 
 * 展示如何使用新的辅助函数来简化界面构建
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\View\Builder;

// ========== 示例1: 传统方式 vs 链式调用方式 ==========

echo "示例1: 创建简单窗口\n";
echo "===================\n\n";

// 传统方式 (仍然支持)
echo "传统方式:\n";
$window1 = Builder::window(['title' => 'Traditional'])
    ->size(400, 300);
echo "✓ 窗口已创建\n\n";

// 新的链式调用方式
echo "链式调用方式:\n";
$window2 = Builder::create()
    ->newWindow(['title' => 'Chained'])
    ->config('size', [400, 300])
    ->get();
echo "✓ 窗口已创建\n\n";

// ========== 示例2: 配置组件属性 ==========

echo "示例2: 配置组件属性\n";
echo "===================\n\n";

$button = Builder::create()
    ->newButton()
    ->config('text', '点击我')
    ->withId('myButton')
    ->addEvent('click', function() {
        echo "按钮被点击!\n";
    })
    ->get();
echo "✓ 按钮已配置 (ID: myButton, 文本: 点击我)\n\n";

// ========== 示例3: 构建复杂布局 ==========

echo "示例3: 构建复杂布局\n";
echo "===================\n\n";

$form = Builder::create()
    ->newGrid(['padded' => true])
    ->children([
        Builder::label()->text('用户名:'),
        Builder::entry()
            ->placeholder('请输入用户名')
            ->id('username'),
        Builder::label()->text('密码:'),
        Builder::passwordEntry()
            ->placeholder('请输入密码')
            ->id('password'),
    ])
    ->get();
echo "✓ 表单网格已创建\n\n";

// ========== 示例4: 组合多个组件 ==========

echo "示例4: 组合多个组件\n";
echo "===================\n\n";

$layout = Builder::create()
    ->newVbox()
    ->children([
        Builder::label()->text('标题'),
        Builder::hbox()->contains([
            Builder::button()->text('确定'),
            Builder::button()->text('取消'),
        ]),
    ])
    ->get();
echo "✓ 垂直布局已创建\n\n";

// ========== 示例5: 实际应用场景 ==========

echo "示例5: 实际应用 - 登录表单\n";
echo "==========================\n\n";

$loginWindow = Builder::create()
    ->newWindow(['title' => '登录'])
    ->config('size', [350, 200])
    ->config('centered', true)
    ->children([
        Builder::vbox(['padded' => true])->contains([
            Builder::label()->text('用户登录'),
            Builder::grid()->form([
                [
                    'label' => Builder::label()->text('账号:'),
                    'control' => Builder::entry()
                        ->id('loginUsername')
                        ->placeholder('请输入账号'),
                ],
                [
                    'label' => Builder::label()->text('密码:'),
                    'control' => Builder::passwordEntry()
                        ->id('loginPassword')
                        ->placeholder('请输入密码'),
                ],
            ]),
            Builder::hbox()->contains([
                Builder::button()
                    ->text('登录')
                    ->id('loginBtn')
                    ->onClick(function() {
                        echo "执行登录逻辑...\n";
                    }),
                Builder::button()
                    ->text('取消')
                    ->onClick(function() {
                        echo "取消登录\n";
                    }),
            ]),
        ])
    ])
    ->get();
echo "✓ 登录窗口已创建\n\n";

// ========== 对比总结 ==========

echo "\n总结\n";
echo "====\n\n";
echo "新的辅助函数优势:\n";
echo "1. ✓ 支持链式调用,代码更流畅\n";
echo "2. ✓ 通过 withId() 设置组件ID\n";
echo "3. ✓ 通过 config() 配置任意属性\n";
echo "4. ✓ 通过 addEvent() 添加事件处理器\n";
echo "5. ✓ 通过 children() 批量添加子组件\n";
echo "6. ✓ 与传统方式完全兼容,不破坏现有代码\n\n";

echo "所有组件创建成功!\n";
