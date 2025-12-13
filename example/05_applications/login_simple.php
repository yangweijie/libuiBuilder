<?php
/**
 * 简化版登录示例 - 使用Builder API
 * 解决GUI不显示问题
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Components\WindowBuilder;
use Kingbes\Libui\View\State\StateManager;

echo "=== 简化版登录示例启动 ===\n";

try {
    // 环境检测
    echo "环境检测...\n";
    
    function isGuiSupported(): bool {
        if (PHP_OS_FAMILY === 'Linux') {
            return !empty(getenv('DISPLAY'));
        }
        return true; // Windows和macOS通常支持GUI
    }
    
    $guiSupported = isGuiSupported();
    echo "  - GUI支持: " . ($guiSupported ? '是' : '否') . "\n";
    
    // 初始化
    echo "初始化App...\n";
    App::init();
    echo "App初始化成功\n";
    
    // 状态管理
    echo "初始化状态管理器...\n";
    $state = StateManager::instance();
    $state->set('username', '');
    $state->set('password', '');
    echo "状态管理器初始化成功\n";
    
    // 创建登录窗口
    echo "创建登录窗口...\n";
    
    // 使用Builder API创建窗口
    $loginWindow = Builder::window()
        ->title('登录窗口')
        ->size(400, 300)
        ->contains([
            Builder::grid()
                ->padded(true)
                ->place(
                    Builder::label()->text('用户名:')->align('end,center'),
                    0, 0
                )
                ->place(
                    Builder::entry()
                        ->id('usernameInput')
                        ->bind('username')
                        ->placeholder('请输入用户名')
                        ->onChange(function($value) {
                            echo "用户名输入: {$value}\n";
                        }),
                    0, 1
                )
                ->place(
                    Builder::label()->text('密码:')->align('end,center'),
                    1, 0
                )
                ->place(
                    Builder::passwordEntry()
                        ->id('passwordInput')
                        ->bind('password')
                        ->placeholder('请输入密码')
                        ->onChange(function($value) {
                            $strength = '弱';
                            if (strlen($value) > 8) $strength = '中';
                            if (strlen($value) > 12 && preg_match('/[A-Z]/', $value) && preg_match('/[0-9]/', $value)) {
                                $strength = '强';
                            }
                            echo "密码强度: {$strength}\n";
                        }),
                    1, 1
                )
                ->place(
                    Builder::hbox()
                        ->padded(true)
                        ->contains([
                            Builder::button()
                                ->id('loginBtn')
                                ->text('登录')
                                ->onClick(function($button) {
                                    $state = StateManager::instance();
                                    $username = $state->get('username');
                                    $password = $state->get('password');
                                    
                                    echo "尝试登录...\n";
                                    echo "用户名: {$username}\n";
                                    echo "密码: " . str_repeat('*', strlen($password)) . "\n";
                                    
                                    if ($username === 'admin' && $password === 'admin') {
                                        echo "✅ 登录成功！\n";
                                    } else {
                                        echo "❌ 登录失败: 用户名或密码错误\n";
                                    }
                                }),
                            
                            Builder::button()
                                ->text('清空')
                                ->onClick(function($button) {
                                    $state = StateManager::instance();
                                    $state->update([
                                        'username' => '',
                                        'password' => ''
                                    ]);
                                    echo "🔄 表单已清空\n";
                                })
                        ]),
                    2, 0, 2
                )
                ->place(
                    Builder::label()
                        ->id('statusLabel')
                        ->text('请输入登录信息')
                        ->align('center'),
                    3, 0, 2
                )
        ]);
    
    echo "登录界面构建完成\n";
    
    if (!$guiSupported) {
        echo "\n🚨 GUI不可用 - 启动调试模式\n";
        echo "运行模拟操作...\n";
        
        // 模拟用户操作
        $state->set('username', 'admin');
        $state->set('password', 'admin');
        
        echo "模拟输入完成\n";
        echo "模拟登录测试...\n";
        
        echo "✅ 调试模式完成\n";
        echo "在图形界面环境中运行此程序将显示GUI窗口\n";
        
    } else {
        echo "\n显示GUI窗口...\n";
        echo "窗口应该正在显示...\n";
        
        // 显示窗口
        $loginWindow->show();
    }
    
} catch (Exception $e) {
    echo "\n❌ 发生异常:\n";
    echo "消息: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . "\n";
    echo "行号: " . $e->getLine() . "\n";
    echo "堆栈:\n" . $e->getTraceAsString() . "\n";
    
} catch (Error $e) {
    echo "\n💥 发生致命错误:\n";
    echo "消息: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . "\n";
    echo "行号: " . $e->getLine() . "\n";
}

echo "\n=== 程序结束 ===\n";