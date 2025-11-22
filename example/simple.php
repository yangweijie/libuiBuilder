<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

$app = Builder::window()
    ->title('完整的基础控件示例')
    ->size(700, 500)
    ->contains([
        Builder::vbox()->contains([
            // 标签演示
            Builder::label()
                ->text('欢迎使用LibUI视图标签系统!')
                ->id('titleLabel'),
            Builder::separator(),
            // 表单区域
            Builder::grid()->form([
                [
                    'label' => Builder::label()->text('姓名:'),
                    'control' => Builder::entry()
                        ->id('nameEntry')
                        ->placeholder('请输入您的姓名')
                        ->maxLength(20)
                        ->validation(fn($value) => !empty(trim($value)))
                        ->onChange(function ($value, $component) {
                            $welcomeLabel = StateManager::instance()->getComponent('welcomeLabel');
                            if ($welcomeLabel) {
                                $text = empty($value) ? '请输入姓名' : "您好, {$value}!";
                                $welcomeLabel->setValue($text);
                            }
                        })
                ],
                [
                    'label' => Builder::label()->text('密码:'),
                    'control' => Builder::passwordEntry()
                        ->id('passwordEntry')
                        ->placeholder('请输入密码')
                        ->minLength(6)
                        ->onChange(function ($value, $component) {
                            $strengthLabel = StateManager::instance()->getComponent('strengthLabel');
                            if ($strengthLabel) {
                                $strength = calculateStrength($value);
                                $strengthLabel->setValue("密码强度: {$strength}");
                            }
                        })
                ],
                [
                    'label' => Builder::label()->text('性别:'),
                    'control' => Builder::combobox()
                        ->id('genderCombo')
                        ->items(['请选择', '男', '女', '其他'])
                        ->onSelected(function ($index, $item, $component) {
                            if ($index > 0) {
                                echo "选择了性别: {$item}\n";
                            }
                        })
                ],
                [
                    'label' => Builder::label()->text('爱好:'),
                    'control' => Builder::editableCombobox()
                        ->items(['读书', '运动', '音乐', '旅行', '编程'])
                        ->placeholder('选择或输入您的爱好')
                ],
            ]),

            // 复选框区域
            Builder::hbox()->contains([
                Builder::checkbox()
                    ->text('同意用户协议')
                    ->id('agreeCheckbox')
                    ->onToggle(function ($checked, $component) {
                            $submitBtn = StateManager::instance()->getComponent('submitBtn');
                            if ($submitBtn) {
                                // 根据协议同意状态启用/禁用提交按钮
                                $submitBtn->getComponent()->setConfig('disabled', !$checked);
                            }
                        }),

                Builder::checkbox()
                    ->text('订阅邮件通知')
                    ->checked(true),
            ]),
            Builder::separator(),
            // 反馈区域
            Builder::label()
                ->text('请输入姓名')
                ->id('welcomeLabel'),
            Builder::label()
                ->text('密码强度: 无')
                ->id('strengthLabel'),
            Builder::separator(),
            // 按钮区域
            Builder::hbox()->contains([
                Builder::button()
                    ->text('提交')
                    ->id('submitBtn')
                    ->onClick(function ($button) {
                        // 收集所有表单数据
                        $name = StateManager::instance()->getComponent('nameEntry')?->getValue();
                        $password = StateManager::instance()->getComponent('passwordEntry')?->getValue();
                        $gender = StateManager::instance()->getComponent('genderCombo')?->getValue();
                        $agreed = StateManager::instance()->getComponent('agreeCheckbox')?->getValue();

                        if (empty($name)) {
                            echo "请输入姓名\n";
                            return;
                        }
                        if (empty($password)) {
                            echo "请输入密码\n";
                            return;
                        }
                        if (!$agreed) {
                            echo "请同意用户协议\n";
                            return;
                        }
                        echo "提交成功!\n";
                        echo "姓名: {$name}\n";
                        echo "性别: " . ($gender['item'] ?? '未选择') . "\n";
                    }),

                Builder::button()
                    ->text('重置')
                    ->onClick(function ($button) {
                        // 重置所有表单控件
                        StateManager::instance()->getComponent('nameEntry')?->getComponent()?->clear();
                        StateManager::instance()->getComponent('passwordEntry')?->getComponent()?->clear();
                        StateManager::instance()->getComponent('genderCombo')?->setValue(0);
                        StateManager::instance()->getComponent('agreeCheckbox')?->setValue(false);
                        // 重置标签
                        StateManager::instance()->getComponent('welcomeLabel')?->setValue('请输入姓名');
                        StateManager::instance()->getComponent('strengthLabel')?->setValue('密码强度: 无');
                    }),
            ]),
        ]),
    ]);

$app->show();