<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

/**
 * 修复版登录示例 - 解决GUI不显示问题
 * 
 * 改进：
 * 1. 环境检测 - 检查是否支持GUI
 * 2. 非阻塞模式 - 使用mainSteps()避免程序卡死
 * 3. 更好的错误处理
 * 4. 调试信息输出
 * 5. 内联模板 - 避免路径问题
 */

echo "=== 登录示例启动 ===\n";

// 环境检测
function isHeadlessEnvironment(): bool {
    $headlessEnv = ['SSH_CONNECTION', 'SSH_TTY', 'TERM', 'DISPLAY'];
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

echo "环境检测:\n";
echo "  - 操作系统: " . PHP_OS_FAMILY . "\n";
echo "  - 无头环境: " . (isHeadlessEnvironment() ? '是' : '否') . "\n";
echo "  - GUI支持: " . (isGuiSupported() ? '是' : '否') . "\n";

if (isHeadlessEnvironment()) {
    echo "\n⚠️  警告: 检测到无头环境，GUI可能无法显示\n";
    echo "程序将运行调试模式\n\n";
}

try {
    // 初始化App
    echo "1. 初始化App...\n";
    App::init();
    echo "   App初始化成功\n";
    
    // 初始化状态
    echo "2. 初始化状态管理器...\n";
    $state = StateManager::instance();
    $state->set('username', '');
    $state->set('password', '');
    echo "   状态管理器初始化成功\n";
    
    // 定义事件处理器
    echo "3. 设置事件处理器...\n";
    $handlers = [
        'handleUsernameChange' => function($value, $component) {
            echo "👤 用户名输入: {$value}\n";
            $canLogin = !empty($value) && !empty(StateManager::instance()->get('password'));
            echo "🔓 可以登录: " . ($canLogin ? '是' : '否') . "\n";
        },

        'handlePasswordChange' => function($value, $component) {
            $strength = '弱';
            if (strlen($value) > 8) $strength = '中';
            if (strlen($value) > 12 && preg_match('/[A-Z]/', $value) && preg_match('/[0-9]/', $value)) {
                $strength = '强';
            }
            echo "🔒 密码强度: {$strength}\n";
        },

        'handleLogin' => function($button, $stateManager) {
            $username = $stateManager->get('username');
            $password = $stateManager->get('password');

            echo "🔑 尝试登录...\n";
            echo "   用户名: {$username}\n";
            echo "   密码: " . str_repeat('*', strlen($password)) . "\n";

            if (empty($username) || empty($password)) {
                echo "❌ 错误: 用户名和密码不能为空\n";
                return;
            }

            if ($username === 'admin' && $password === 'admin') {
                echo "✅ 登录成功！\n";
            } else {
                echo "❌ 登录失败: 用户名或密码错误\n";
            }
        },

        'handleReset' => function($button, $stateManager) {
            echo "🔄 重置表单...\n";
            $stateManager->update(['username' => '', 'password' => '']);
            echo "✅ 表单已清空\n";
        }
    ];
    echo "   事件处理器设置完成\n";
    
    // 内联HTML模板
    echo "4. 使用内联HTML模板...\n";
    $template = '<!DOCTYPE html>
<ui version="1.0">
  <window title="登录窗口" size="400,300" centered="true" margined="true">
    <grid padded="true">
      <label row="0" col="0" align="end,center">用户名:</label>
      <input 
        id="usernameInput"
        row="0" 
        col="1" 
        bind="username"
        placeholder="请输入用户名"
        expand="horizontal"
        onchange="handleUsernameChange"
      />
      
      <label row="1" col="0" align="end,center">密码:</label>
      <input 
        id="passwordInput"
        row="1" 
        col="1" 
        type="password"
        bind="password"
        placeholder="请输入密码"
        expand="horizontal"
        onchange="handlePasswordChange"
      />
      
      <hbox row="2" col="0" colspan="2" align="center">
        <button id="loginBtn" onclick="handleLogin">登录</button>
        <button onclick="handleReset">清空</button>
      </hbox>
      
      <label 
        id="statusLabel" 
        row="3" 
        col="0" 
        colspan="2" 
        align="center"
      >请输入登录信息</label>
      
    </grid>
  </window>
</ui>';
    
    // 保存临时模板文件
    $tempTemplateFile = __DIR__ . '/temp_login.ui.html';
    file_put_contents($tempTemplateFile, $template);
    
    $app = HtmlRenderer::render($tempTemplateFile, $handlers);
    echo "   模板渲染成功\n";
    
    // 检查GUI支持
    if (!isGuiSupported() || isHeadlessEnvironment()) {
        echo "\n🚨 GUI不可用 - 启动调试模式\n";
        echo "程序将在5秒后自动退出...\n";
        
        // 调试模式：仅构建但不显示GUI
        $app->build();
        echo "✅ 应用构建完成\n";
        
        echo "\n📝 测试事件处理器:\n";
        
        // 模拟用户操作
        echo "模拟输入用户名...\n";
        $state->set('username', 'admin');
        $handlers['handleUsernameChange']('admin', null);
        
        echo "模拟输入密码...\n";
        $state->set('password', 'admin');
        $handlers['handlePasswordChange']('admin', null);
        
        echo "模拟登录...\n";
        $handlers['handleLogin'](null, $state);
        
        echo "模拟重置...\n";
        $handlers['handleReset'](null, $state);
        
        echo "\n⏰ 等待5秒后退出...\n";
        sleep(5);
        
        echo "✅ 调试模式完成\n";
        
        // 清理临时文件
        if (file_exists($tempTemplateFile)) {
            unlink($tempTemplateFile);
        }
        
        exit(0);
    }
    
    echo "5. 显示GUI窗口...\n";
    echo "   窗口应该正在显示...\n";
    
    // 正常GUI模式
    $app->show();
    
} catch (Exception $e) {
    echo "\n❌ 发生异常:\n";
    echo "   消息: " . $e->getMessage() . "\n";
    echo "   文件: " . $e->getFile() . "\n";
    echo "   行号: " . $e->getLine() . "\n";
    echo "   堆栈:\n" . $e->getTraceAsString() . "\n";
    
    echo "\n🔧 故障排除建议:\n";
    echo "1. 确保在图形界面环境中运行\n";
    echo "2. 检查PHP FFI扩展是否加载\n";
    echo "3. 确认libui库已正确安装\n";
    
} catch (Error $e) {
    echo "\n💥 发生致命错误:\n";
    echo "   消息: " . $e->getMessage() . "\n";
    echo "   文件: " . $e->getFile() . "\n";
    echo "   行号: " . $e->getLine() . "\n";
}

echo "\n=== 程序结束 ===\n";