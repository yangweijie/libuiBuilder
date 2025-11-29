<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 初始化状态
$state = StateManager::instance();
$state->set('username', '');
$state->set('password', '');

// 定义事件处理器
$handlers = [
    'handleUsernameChange' => function($value, $component) {
        echo "用户名输入: {$value}\n";
        
        // 访问其他组件
        $loginBtn = StateManager::instance()->getComponent('loginBtn');
        $passwordInput = StateManager::instance()->getComponent('passwordInput');
        
        // 根据输入启用/禁用登录按钮（逻辑示例）
        $canLogin = !empty($value) && !empty($passwordInput?->getValue());
        echo "可以登录: " . ($canLogin ? '是' : '否') . "\n";
    },
    
    'handlePasswordChange' => function($value, $component) {
        // 计算密码强度
        $strength = '弱';
        if (strlen($value) > 8) {
            $strength = '中';
        }
        if (strlen($value) > 12 && preg_match('/[A-Z]/', $value) && preg_match('/[0-9]/', $value)) {
            $strength = '强';
        }
        
        $statusLabel = StateManager::instance()->getComponent('statusLabel');
        if ($statusLabel) {
            $statusLabel->setValue("密码强度: {$strength}");
        }
    },
    
    'handleLogin' => function($button, $stateManager) {
        $username = $stateManager->get('username');
        $password = $stateManager->get('password');
        
        if (empty($username) || empty($password)) {
            echo "用户名和密码不能为空\n";
            return;
        }
        
        // 模拟登录
        if ($username === 'admin' && $password === 'admin') {
            echo "登录成功！\n";
            
            $statusLabel = StateManager::instance()->getComponent('statusLabel');
            if ($statusLabel) {
                $statusLabel->setValue("登录成功！");
            }
        } else {
            echo "用户名或密码错误\n";
            
            $statusLabel = StateManager::instance()->getComponent('statusLabel');
            if ($statusLabel) {
                $statusLabel->setValue("登录失败：用户名或密码错误");
            }
        }
    },
    
    'handleReset' => function($button, $stateManager) {
        // 清空所有输入
        $stateManager->update([
            'username' => '',
            'password' => ''
        ]);
        
        // 直接操作组件
        StateManager::instance()->getComponent('usernameInput')?->setValue('');
        StateManager::instance()->getComponent('passwordInput')?->setValue('');
        
        $statusLabel = StateManager::instance()->getComponent('statusLabel');
        if ($statusLabel) {
            $statusLabel->setValue("请输入登录信息");
        }
        
        echo "表单已清空\n";
    }
];

// 从 HTML 渲染
$app = HtmlRenderer::render(__DIR__ . '/views/login.ui.html', $handlers);
$app->show();
