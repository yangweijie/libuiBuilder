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

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder\Builder;
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

// 设置全局StateManager到Builder类
Builder::setStateManager($state);

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

function getPasswordStrength($password) {
    if (empty($password)) return '无';
    
    $strength = 0;
    if (strlen($password) >= 6) $strength++;
    if (strlen($password) >= 8) $strength++;
    if (preg_match('/[a-z]/', $password)) $strength++;
    if (preg_match('/[A-Z]/', $password)) $strength++;
    if (preg_match('/[0-9]/', $password)) $strength++;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $strength++;
    
    $levels = ['无', '弱', '一般', '中等', '强', '很强', '极强'];
    return $levels[$strength] ?? '极强';
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
                            ->bind('formData.username')
                            ->onChange(function($value, $component, $stateManager) use ($state) {
                                echo "[DEBUG] 用户名输入变化: '$value'\n";
                                echo "[DEBUG] StateManager实例ID: " . spl_object_hash($stateManager) . "\n";
                                echo "[DEBUG] 全局StateManager实例ID: " . spl_object_hash(StateManager::instance()) . "\n";
                                echo "[DEBUG] 当前状态: " . json_encode($stateManager->get('formData')) . "\n";
                                
                                $error = validateUsername($value);
                                $errorLabel = $stateManager->getComponent('usernameError');
                                if ($errorLabel) {
                                    $errorLabel->setValue($error);
                                }
                                
                                checkFormValidity($stateManager);
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
                            ->bind('formData.email')
                            ->onChange(function($value, $component, $stateManager) use ($state) {
                                $error = validateEmail($value);
                                $errorLabel = $stateManager->getComponent('emailError');
                                if ($errorLabel) {
                                    $errorLabel->setValue($error);
                                }
                                
                                checkFormValidity($stateManager);
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
                        'control' => Builder::entry()
                            ->id('passwordInput')
                            ->password()
                            ->bind('formData.password')
                            ->placeholder('请输入密码')
                            ->onChange(function($value, $component, $stateManager) use ($state) {
                                $strength = getPasswordStrength($value);
                                $strengthLabel = $stateManager->getComponent('passwordStrength');
                                if ($strengthLabel) {
                                    $strengthLabel->setValue($strength);
                                }
                                
                                $confirmPassword = $stateManager->get('formData.confirmPassword');
                                if (!empty($confirmPassword)) {
                                    $confirmError = ($value === $confirmPassword) ? '' : '两次输入的密码不一致';
                                    $confirmErrorLabel = $stateManager->getComponent('confirmPasswordError');
                                    if ($confirmErrorLabel) {
                                        $confirmErrorLabel->setValue($confirmError);
                                    }
                                }
                                
                                checkFormValidity($stateManager);
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
                        'control' => Builder::entry()
                            ->id('confirmPasswordInput')
                            ->password()
                            ->placeholder('请再次输入密码')
                            ->bind('formData.confirmPassword')
                            ->onChange(function($value, $component, $stateManager) use ($state) {

                                $password = $stateManager->get('formData.password');
                                $error = ($value === $password) ? '' : '两次输入的密码不一致';
                                $confirmErrorLabel = $stateManager->getComponent('confirmPasswordError');
                                if ($confirmErrorLabel) {
                                    $confirmErrorLabel->setValue($error);
                                }
                                
                                checkFormValidity($stateManager);
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
                            ->bind('formData.age')
                            ->onChange(function($value, $component, $stateManager) use ($state) {

                                $error = validateAge($value);
                                $errorLabel = $stateManager->getComponent('ageError');
                                if ($errorLabel) {
                                    $errorLabel->setValue($error);
                                }
                                
                                checkFormValidity($stateManager);
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
                    ->bind('formData.agreed') ,
                
                Builder::separator(),
                
                // 提交按钮
                Builder::button()
                    ->text('提交注册')
                    ->id('submitBtn')
                    ->onClick(function($button) {
                        $stateManager = $button->getStateManager();
                        $formData = $stateManager->get('formData');
                        var_dump($formData);

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
                    ->onClick(function($button) {
                        $stateManager = $button->getStateManager();
                        
                        // 重置所有字段
                        $stateManager->set('formData', [
                            'username' => '',
                            'email' => '',
                            'password' => '',
                            'confirmPassword' => '',
                            'age' => '',
                            'agreed' => false
                        ]);
                        
                        // 清空UI组件
                        $stateManager->getComponent('usernameInput')?->setValue('');
                        $stateManager->getComponent('emailInput')?->setValue('');
                        $stateManager->getComponent('passwordInput')?->setValue('');
                        $stateManager->getComponent('confirmPasswordInput')?->setValue('');
                        $stateManager->getComponent('ageInput')?->setValue(25);
                        $stateManager->getComponent('agreeCheckbox')?->setValue(false);
                        
                        // 清空错误信息
                        $errorLabels = ['usernameError', 'emailError', 'passwordStrength', 'confirmPasswordError', 'ageError'];
                        foreach ($errorLabels as $labelId) {
                            $label = $stateManager->getComponent($labelId);
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
    
    $submitBtn = $state->getComponent('submitBtn');
    if ($submitBtn) {
        // 这里应该调用 setEnabled 方法，但 Builder 模式可能需要不同的API
        // $submitBtn->setEnabled($isValid);
        echo "表单有效性: " . ($isValid ? '有效' : '无效') . "\n";
    }
}

$app->show();
App::main();