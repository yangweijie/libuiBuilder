<?php
/**
 * Builder 快捷函数实际应用示例
 * 展示如何使用全局快捷函数简化代码
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helper.php';

use Kingbes\Libui\App;

App::init();

echo "========== 对比示例 ==========\n\n";

// ========== 传统方式 (啰嗦) ==========
echo "传统方式代码:\n";
echo "-------------------\n";
echo <<<'CODE'
use Kingbes\Libui\View\Builder;

$window = Builder::window(['title' => '登录'])
    ->size(400, 300)
    ->contains([
        Builder::vbox()->contains([
            Builder::grid()->form([
                [
                    'label' => Builder::label()->text('用户名:'),
                    'control' => Builder::entry()
                        ->id('username')
                        ->bind('username')
                        ->placeholder('请输入用户名')
                ],
                [
                    'label' => Builder::label()->text('密码:'),
                    'control' => Builder::passwordEntry()
                        ->id('password')
                        ->bind('password')
                        ->placeholder('请输入密码')
                ]
            ])
        ])
    ]);
CODE;
echo "\n\n";

// ========== 新方式 (简洁) ==========
echo "快捷函数方式代码:\n";
echo "-------------------\n";
echo <<<'CODE'
$window = window(['title' => '登录'])
    ->size(400, 300)
    ->contains([
        vbox()->contains([
            grid()->form([
                input('用户名', 'username', placeholder: '请输入用户名'),
                input('密码', 'password', type: 'password', placeholder: '请输入密码')
            ])
        ])
    ]);
CODE;
echo "\n\n";

echo "代码行数对比:\n";
echo "- 传统方式: 20 行\n";
echo "- 快捷方式: 9 行\n";
echo "- 减少: 55%\n\n";

// ========== 实际构建示例 ==========
echo "========== 实际构建测试 ==========\n\n";

$loginWindow = window(['title' => '用户登录'])
    ->size(400, 350)
    ->centered(true)
    ->contains([
        vbox(['padded' => true])->contains([
            label()->text('欢迎登录系统'),
            hSeparator(),
            
            grid()->form([
                input('用户名', 'username', placeholder: '请输入账号'),
                input('密码', 'password', type: 'password', placeholder: '请输入密码'),
                select('角色', 'role', ['管理员', '普通用户', '访客']),
            ]),
            
            vSeparator(),
            
            hbox()->contains([
                button()->text('登录')->onClick(function() {
                    echo "登录按钮被点击\n";
                    echo "用户名: " . state('username') . "\n";
                    echo "角色: " . (state('role')['item'] ?? '未选择') . "\n";
                }),
                button()->text('重置')->onClick(function() {
                    echo "表单已重置\n";
                    state('username', '');
                    state('password', '');
                    state('role', null);
                }),
            ]),
        ])
    ]);

echo "✓ 登录窗口构建成功\n\n";

// ========== 复杂表单示例 ==========
$complexForm = window(['title' => '用户注册'])
    ->size(500, 600)
    ->contains([
        vbox(['padded' => true])->contains([
            label()->text('新用户注册'),
            separator(),
            
            grid()->form([
                input('姓名', 'name', placeholder: '请输入真实姓名'),
                input('邮箱', 'email', placeholder: 'user@example.com'),
                input('手机', 'phone', placeholder: '13800138000'),
                input('密码', 'pwd', type: 'password', placeholder: '至少6位'),
                input('确认密码', 'pwd2', type: 'password'),
                select('性别', 'gender', ['男', '女', '保密']),
                select('兴趣', 'interests', ['编程', '音乐', '运动'], type: 'radio'),
                input('个人简介', 'bio', type: 'textarea'),
            ]),
            
            separator(),
            
            hbox()->contains([
                checkbox()->text('我已阅读并同意用户协议')->id('agree'),
            ]),
            
            hbox()->contains([
                button()->text('提交注册'),
                button()->text('清空表单'),
                button()->text('返回登录'),
            ]),
        ])
    ]);

echo "✓ 注册表单构建成功\n\n";

// ========== 总结 ==========
echo "========== 总结 ==========\n\n";
echo "快捷函数优势:\n";
echo "1. ✓ 消除 Builder:: 前缀噪音\n";
echo "2. ✓ 代码量减少 50%+\n";
echo "3. ✓ 保持完全兼容性\n";
echo "4. ✓ input() 和 select() 自动创建表单行\n";
echo "5. ✓ 自动绑定 ID 和状态\n";
echo "6. ✓ 支持命名参数，代码更清晰\n\n";

echo "所有示例构建成功!\n";
