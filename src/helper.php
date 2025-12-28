<?php

use Kingbes\Libui\View\State\StateManager;

// 密码强度计算函数
function calculateStrength(#[\SensitiveParameter] $password): string
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

// ========== Builder 快捷函数 ==========

use Kingbes\Libui\View\Builder;

// 窗口和容器
function window(array $config = [])
{
    return Builder::window($config);
}

function vbox(array $config = [])
{
    return Builder::vbox($config);
}

function hbox(array $config = [])
{
    return Builder::hbox($config);
}

function grid(array $config = [])
{
    return Builder::grid($config);
}

function tab(array $config = [])
{
    return Builder::tab($config);
}

// 基础控件
function button(array $config = [])
{
    return Builder::button($config);
}

function label(array $config = [])
{
    return Builder::label($config);
}

function entry(array $config = [])
{
    return Builder::entry($config);
}

function checkbox(array $config = [])
{
    return Builder::checkbox($config);
}

function combobox(array $config = [])
{
    return Builder::combobox($config);
}

// 输入控件
function textarea(array $config = [])
{
    return Builder::textarea($config);
}

function spinbox(array $config = [])
{
    return Builder::spinbox($config);
}

function slider(array $config = [])
{
    return Builder::slider($config);
}

function radio(array $config = [])
{
    return Builder::radio($config);
}

// 其他控件
function progressBar(array $config = [])
{
    return Builder::progressBar($config);
}

function table(array $config = [])
{
    return Builder::table($config);
}

function canvas(array $config = [])
{
    return Builder::canvas($config);
}

function separator()
{
    return Builder::separator();
}

function menu()
{
    return Builder::menu();
}

function group(array $config = [])
{
    return Builder::group($config);
}

// 便捷方法
function passwordEntry(array $config = [])
{
    return Builder::passwordEntry($config);
}

function editableCombobox(array $config = [])
{
    return Builder::editableCombobox($config);
}

function hSeparator(array $config = [])
{
    return Builder::hSeparator($config);
}

function vSeparator(array $config = [])
{
    return Builder::vSeparator($config);
}

/**
 * 快捷输入字段创建函数
 *
 * @param string $label 标签文本
 * @param string $id 组件ID（同时用于绑定状态）
 * @param string $type 输入类型：'text', 'password', 'textarea'
 * @param string $placeholder 占位符文本
 * @param array $extra 额外配置
 * @return array 包含 label 和 control 的表单行
 */
function input(string $label, string $id, string $type = 'text', string $placeholder = '', array $extra = []): array
{
    $control = match ($type) {
        'password' => Builder::passwordEntry(),
        'textarea' => Builder::textarea(),
        default => Builder::entry(),
    };

    $control->id($id)->bind($id);

    if ($placeholder) {
        $control->placeholder($placeholder);
    }

    foreach ($extra as $key => $value) {
        $control->setConfig($key, $value);
    }

    return [
        'label' => Builder::label()->text($label . ':'),
        'control' => $control,
    ];
}

/**
 * 快捷选择字段创建函数
 *
 * @param string $label 标签文本
 * @param string $id 组件ID
 * @param array $items 选项列表
 * @param string $type 选择类型：'combobox', 'radio', 'checkbox'
 * @param array $extra 额外配置
 * @return array 包含 label 和 control 的表单行
 */
function select(string $label, string $id, array $items, string $type = 'combobox', array $extra = []): array
{
    $control = match ($type) {
        'radio' => Builder::radio()->items($items),
        'checkbox' => Builder::checkbox(),
        'editable' => Builder::editableCombobox()->items($items),
        default => Builder::combobox()->items($items),
    };

    $control->id($id)->bind($id);

    foreach ($extra as $key => $value) {
        $control->setConfig($key, $value);
    }

    return [
        'label' => Builder::label()->text($label . ':'),
        'control' => $control,
    ];
}
