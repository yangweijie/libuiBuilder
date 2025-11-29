<?php

use Kingbes\Libui\View\State\StateManager;

// 密码强度计算函数
function calculateStrength($password): string
{
    $score = 0;
    $feedback = [];
    // 长度检查
    if (strlen($password) >= 6) {
        $score += 1;
    } else {
        $feedback[] = '长度至少6位';

    }
    if (strlen($password) >= 8) {
        $score += 1;
    }
    // 包含小写字母
    if (preg_match('/[a-z]/', $password)) {
        $score += 1;
    } else {
        $feedback[] = '缺少小写字母';
    }

    // 包含大写字母
    if (preg_match('/[A-Z]/', $password)) {
        $score += 1;
    } else {
        $feedback[] = '缺少大写字母';
    }
    // 包含数字
    if (preg_match('/[0-9]/', $password)) {
        $score += 1;
    } else {
        $feedback[] = '缺少数字';
    }

    // 包含特殊字符
    if (preg_match('/[^a-zA-Z0-9]/', $password)) {
        $score += 1;
    } else {
        $feedback[] = '缺少特殊字符';
    }

    switch ($score) {
        case 0:
        case 1:
        case 2:
            return '弱';
        case 3:
        case 4:
            return '中';
        case 5:
        case 6:
            return '强';
        default:
            return '强';
    }
}

/**
 * 状态管理辅助函数
 * 
 * 用法:
 * - state() - 获取状态管理器实例
 * - state('key') - 获取状态值
 * - state('key', 'value') - 设置状态值
 * - state(['key1' => 'value1', 'key2' => 'value2']) - 批量设置状态
 */
function state($key = null, $value = null)
{
    $manager = StateManager::instance();
    
    // 无参数时返回管理器实例
    if ($key === null) {
        return $manager;
    }
    
    // 批量设置
    if (is_array($key)) {
        foreach ($key as $k => $v) {
            $manager->set($k, $v);
        }
        return;
    }
    
    // 设置值
    if ($value !== null) {
        $manager->set($key, $value);
        return;
    }
    
    // 获取值
    return $manager->get($key);
}

/**
 * 状态监听辅助函数
 * 
 * 用法:
 * watch('key', function($new, $old) {
 *     // 状态变化时的回调
 * });
 */
function watch(string $key, callable $callback)
{
    StateManager::instance()->watch($key, $callback);
}