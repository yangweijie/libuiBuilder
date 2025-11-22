<?php

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