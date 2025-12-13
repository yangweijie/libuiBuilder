<?php
/**
 * 表单验证示例 - Builder API 模式
 * 
 * 演示内容：
 * - 表单输入验证
 * - 实时验证反馈
 * - 密码强度检查
 * - 表单提交处理
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

$state = StateManager::instance();
$state->set('formData', [
    'username' => '',
    'email' => '',
    'password' => '',
    'confirmPassword' => '',
    'age' => '',
    'agreed' => false
]);

// 验证函数
function validateUsername($username) {
    if (empty($username)) return '用户名不能为空';
    if (strlen($username) < 3) return '用户名至少3个字符';
    if (strlen($username) > 20) return '用户名不能超过20个字符';
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) return '用户名只能包含字母、数字和下划线';
    return '';
}

function validateEmail($email) {
    if (empty($email)) return '邮箱不能为空';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return '邮箱格式不正确';
    return '';
}

function validatePassword($password) {
    if (empty($password)) return '密码不能为空';
    if (strlen($password) < 6) return '密码至少6个字符';
    return '';
}

function validateAge($age) {
    if (empty($age)) return '年龄不能为空';
    if (!is_numeric($age)) return '年龄必须是数字';
    if ($age < 18 || $age > 100) return '年龄必须在18-100之间';
    return '';
}

$app = Builder::window()
    ->title('表单验证示例 - Builder API')
    ->size(500, 600)
    ->contains([
        Builder::vbox()
            ->padded(true)
            ->contains([
                Builder::label()
                    ->text('用户注册表单')
                    ->align('center')
                    ->id('titleLabel'),
                
                Builder::separator(),
                
                // 用户名输入
                Builder::grid()->form([
                    [
                        'label' => Builder::label()->text('用户名:'),
                        'control' => Builder::entry()
                            ->id('usernameInput')
                            ->placeholder('请输入用户名')
                            ->onChange(function($value, $component) use ($state) {
                                $state->set('formData.username', $value);
                                
                                $error = validateUsername($value);
                                $errorLabel = StateManager::instance()->getComponent('usernameError');
                                if ($errorLabel) {
                                    $errorLabel->setValue($error);
                                }
                                
                                // 检查整体表单有效性
                                checkFormValidity($state);
                            })
                    ],
                    [
                        'label' => Builder::label()->text(''),
                        'control' => Builder::label()
                            ->text('')
                            ->id('usernameError')
                    ],
                    [
                        'label' => Builder::label()->text('邮箱:'),
                        'control' => Builder::entry()
                            ->id('emailInput')
                            ->placeholder('请输入邮箱地址')
                            ->onChange(function($value, $component) use ($state) {
                                $state->set('formData.email', $value);
                                
                                $error = validateEmail($value);
                                $errorLabel = StateManager::instance()->getComponent('emailError');
                                if ($errorLabel) {
                                    $errorLabel->setValue($error);
                                }
                                
                                checkFormValidity($state);
                            })
                    ],
                    [
                        'label' => Builder::label()->text(''),
                        'control' => Builder::label()
                            ->text('')
                            ->id('emailError')
                    ],
                    [
                        'label' => Builder::label()->text('密码:'),
                        'control' => Builder::passwordEntry()
                            ->id('passwordInput')
                            ->placeholder('请输入密码')
                            ->onChange(function($value, $component) use ($state) {
                                $state->set('formData.password', $value);
                                
                                // 更新密码强度
                                $strength = calculateStrength($value);
                                $strengthLabel = StateManager::instance()->getComponent('passwordStrength');
                                if ($strengthLabel) {
                                    $strengthLabel->setValue("密码强度: {$strength}");
                                }
                                
                                // 验证确认密码
                                $confirmPassword = $state->get('formData.confirmPassword');
                                if (!empty($confirmPassword)) {
                                    $confirmError = ($value === $confirmPassword) ? '' : '两次输入的密码不一致';
                                    $confirmErrorLabel = StateManager::instance()->getComponent('confirmPasswordError');
                                    if ($confirmErrorLabel) {
                                        $confirmErrorLabel->setValue($confirmError);
                                    }
                                }
                                
                                checkFormValidity($state);
                            })
                    ],
                    [
                        'label' => Builder::label()->text(''),
                        'control' => Builder::label()
                            ->text('密码强度: 无')
                            ->id('passwordStrength')
                    ],
                    [
                        'label' => Builder::label()->text('确认密码:'),
                        'control' => Builder::passwordEntry()
                            ->id('confirmPasswordInput')
                            ->placeholder('请再次输入密码')
                            ->onChange(function($value, $component) use ($state) {
                                $state->set('formData.confirmPassword', $value);
                                
                                $password = $state->get('formData.password');
                                $error = ($value === $password) ? '' : '两次输入的密码不一致';
                                
                                $errorLabel = StateManager::instance()->getComponent('confirmPasswordError');
                                if ($errorLabel) {
                                    $errorLabel->setValue($error);
                                }
                                
                                checkFormValidity($state);
                            })
                    ],
                    [
                        'label' => Builder::label()->text(''),
                        'control' => Builder::label()
                            ->text('')
                            ->id('confirmPasswordError')
                    ],
                    [
                        'label' => Builder::label()->text('年龄:'),
                        'control' => Builder::spinbox()
                            ->id('ageInput')
                            ->range(18, 100)
                            ->value(25)
                            ->onChange(function($value, $component) use ($state) {
                                $state->set('formData.age', $value);
                                
                                $error = validateAge($value);
                                $errorLabel = StateManager::instance()->getComponent('ageError');
                                if ($errorLabel) {
                                    $errorLabel->setValue($error);
                                }
                                
                                checkFormValidity($state);
                            })
                    ],
                    [
                        'label' => Builder::label()->text(''),
                        'control' => Builder::label()
                            ->text('')
                            ->id('ageError')
                    ],
                ]),
                
                // 用户协议
                Builder::checkbox()
                    ->id('agreeCheckbox')
                    ->text('我同意用户协议和隐私政策')
                    ->onToggle(function($checked, $component) use ($state) {
                        $state->set('formData.agreed', $checked);
                        checkFormValidity($state);
                    }),
                
                Builder::separator(),
                
                // 提交按钮
                Builder::button()
                    ->text('提交注册')
                    ->id('submitBtn')
                    ->onClick(function($button, $state) {
                        $formData = $state->get('formData');
                        
                        // 最终验证
                        $errors = [
                            'username' => validateUsername($formData['username']),
                            'email' => validateEmail($formData['email']),
                            'password' => validatePassword($formData['password']),
                            'age' => validateAge($formData['age']),
                        ];
                        
                        $hasErrors = false;
                        foreach ($errors as $field => $error) {
                            if (!empty($error)) {
                                $hasErrors = true;
                                break;
                            }
                        }
                        
                        if (!$formData['agreed']) {
                            echo "请同意用户协议\n";
                            return;
                        }
                        
                        if ($hasErrors) {
                            echo "请修正表单错误\n";
                            return;
                        }
                        
                        echo "注册成功！\n";
                        echo "用户名: {$formData['username']}\n";
                        echo "邮箱: {$formData['email']}\n";
                        echo "年龄: {$formData['age']}\n";
                    }),
                
                // 重置按钮
                Builder::button()
                    ->text('重置表单')
                    ->onClick(function($button, $state) {
                        // 重置所有字段
                        $state->set('formData', [
                            'username' => '',
                            'email' => '',
                            'password' => '',
                            'confirmPassword' => '',
                            'age' => '',
                            'agreed' => false
                        ]);
                        
                        // 清空UI组件
                        StateManager::instance()->getComponent('usernameInput')?->setValue('');
                        StateManager::instance()->getComponent('emailInput')?->setValue('');
                        StateManager::instance()->getComponent('passwordInput')?->setValue('');
                        StateManager::instance()->getComponent('confirmPasswordInput')?->setValue('');
                        StateManager::instance()->getComponent('ageInput')?->setValue(25);
                        StateManager::instance()->getComponent('agreeCheckbox')?->setValue(false);
                        
                        // 清空错误信息
                        $errorLabels = ['usernameError', 'emailError', 'passwordStrength', 'confirmPasswordError', 'ageError'];
                        foreach ($errorLabels as $labelId) {
                            $label = StateManager::instance()->getComponent($labelId);
                            if ($label) {
                                $label->setValue('');
                            }
                        }
                        
                        echo "表单已重置\n";
                    }),
            ])
    ]);

// 检查表单整体有效性
function checkFormValidity($state) {
    $formData = $state->get('formData');
    
    $isValid = !empty($formData['username']) 
        && !empty($formData['email']) 
        && !empty($formData['password'])
        && !empty($formData['confirmPassword'])
        && !empty($formData['age'])
        && validateUsername($formData['username']) === ''
        && validateEmail($formData['email']) === ''
        && validatePassword($formData['password']) === ''
        && $formData['password'] === $formData['confirmPassword']
        && validateAge($formData['age']) === ''
        && $formData['agreed'] === true;
    
    $submitBtn = StateManager::instance()->getComponent('submitBtn');
    if ($submitBtn) {
        // 这里应该调用 setEnabled 方法，但 Builder 模式可能需要不同的API
        // $submitBtn->setEnabled($isValid);
        echo "表单有效性: " . ($isValid ? '有效' : '无效') . "\n";
    }
}

$app->show();