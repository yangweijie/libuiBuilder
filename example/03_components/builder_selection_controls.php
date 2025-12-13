<?php
/**
 * 选择控件示例 - Builder API 模式
 * 
 * 演示内容：
 * - Combobox 组件使用
 * - Radio 单选按钮组
 * - Checkbox 复选框
 * - 事件处理和数据获取
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

$state = StateManager::instance();
$state->set('selections', [
    'country' => '',
    'language' => '',
    'gender' => '',
    'interests' => [],
    'newsletter' => false,
    'notifications' => false
]);

// 模拟数据
$countries = ['中国', '美国', '日本', '韩国', '英国', '德国', '法国', '加拿大'];
$languages = ['中文', 'English', '日本語', '한국어', 'Español', 'Français'];
$interestsOptions = ['编程', '设计', '音乐', '运动', '阅读', '旅行', '摄影', '游戏'];

$app = Builder::window()
    ->title('选择控件演示 - Builder API')
    ->size(600, 700)
    ->contains([
        Builder::vbox()
            ->padded(true)
            ->contains([
                // 标题
                Builder::label()
                    ->text('选择控件组件演示')
                    ->align('center')
                    ->id('titleLabel'),
                
                Builder::separator(),
                
                // 下拉选择框演示
                Builder::group()
                    ->title('下拉选择框 (Combobox)')
                    ->margined(true)
                    ->contains([
                        Builder::vbox()->padded(true)->contains([
                            Builder::grid()->form([
                                [
                                    'label' => Builder::label()->text('选择国家:'),
                                    'control' => Builder::combobox()
                                        ->id('countryCombo')
                                        ->items($countries)
                                        ->selected(0)
                                        ->onSelected(function($index, $item, $component) use ($state) {
                                            $state->set('selections.country', $item);
                                            echo "选择国家: {$item}\n";
                                            
                                            // 更新相关显示
                                            $countryLabel = StateManager::instance()->getComponent('countrySelection');
                                            if ($countryLabel) {
                                                $countryLabel->setValue("您选择了: {$item}");
                                            }
                                        })
                                ],
                                [
                                    'label' => Builder::label()->text('选择语言:'),
                                    'control' => Builder::combobox()
                                        ->id('languageCombo')
                                        ->items($languages)
                                        ->selected(0)
                                        ->onSelected(function($index, $item, $component) use ($state) {
                                            $state->set('selections.language', $item);
                                            echo "选择语言: {$item}\n";
                                            
                                            $languageLabel = StateManager::instance()->getComponent('languageSelection');
                                            if ($languageLabel) {
                                                $languageLabel->setValue("语言偏好: {$item}");
                                            }
                                        })
                                ],
                            ]),
                            
                            Builder::label()->text('当前选择:'),
                            Builder::label()->text('')->id('countrySelection'),
                            Builder::label()->text('')->id('languageSelection'),
                        ])
                    ]),
                
                // 单选按钮演示
                Builder::group()
                    ->title('单选按钮 (Radio)')
                    ->margined(true)
                    ->contains([
                        Builder::vbox()->padded(true)->contains([
                            Builder::label()->text('性别选择:'),
                            Builder::radio()
                                ->id('genderRadio')
                                ->items(['男', '女', '其他'])
                                ->selected(0),
                            
                            Builder::label()->text('')->id('genderSelection'),
                        ])
                    ]),
                
                // 复选框演示
                Builder::group()
                    ->title('复选框 (Checkbox)')
                    ->margined(true)
                    ->contains([
                        Builder::vbox()->padded(true)->contains([
                            Builder::label()->text('兴趣爱好 (可多选):'),
                            // 创建复选框数组
                            Builder::vbox()->contains([
                                Builder::hbox()->contains([
                                    Builder::checkbox()
                                        ->text('编程')
                                        ->id('interest_0')
                                        ->onToggle(function($checked, $component) use ($state, $interestsOptions) {
                                            updateInterests($state, $interestsOptions, 0, $checked);
                                        }),
                                    Builder::checkbox()
                                        ->text('设计')
                                        ->id('interest_1')
                                        ->onToggle(function($checked, $component) use ($state, $interestsOptions) {
                                            updateInterests($state, $interestsOptions, 1, $checked);
                                        }),
                                ]),
                                Builder::hbox()->contains([
                                    Builder::checkbox()
                                        ->text('音乐')
                                        ->id('interest_2')
                                        ->onToggle(function($checked, $component) use ($state, $interestsOptions) {
                                            updateInterests($state, $interestsOptions, 2, $checked);
                                        }),
                                    Builder::checkbox()
                                        ->text('运动')
                                        ->id('interest_3')
                                        ->onToggle(function($checked, $component) use ($state, $interestsOptions) {
                                            updateInterests($state, $interestsOptions, 3, $checked);
                                        }),
                                ]),
                                Builder::hbox()->contains([
                                    Builder::checkbox()
                                        ->text('阅读')
                                        ->id('interest_4')
                                        ->onToggle(function($checked, $component) use ($state, $interestsOptions) {
                                            updateInterests($state, $interestsOptions, 4, $checked);
                                        }),
                                    Builder::checkbox()
                                        ->text('旅行')
                                        ->id('interest_5')
                                        ->onToggle(function($checked, $component) use ($state, $interestsOptions) {
                                            updateInterests($state, $interestsOptions, 5, $checked);
                                        }),
                                ]),
                            ]),
                            
                            Builder::label()->text('')->id('interestsSelection'),
                        ])
                    ]),
                
                // 开关选项
                Builder::group()
                    ->title('开关选项')
                    ->margined(true)
                    ->contains([
                        Builder::vbox()->padded(true)->contains([
                            Builder::checkbox()
                                ->id('newsletterCheckbox')
                                ->text('订阅邮件通知')
                                ->checked(true)
                                ->onToggle(function($checked, $component) use ($state) {
                                    $state->set('selections.newsletter', $checked);
                                    echo "邮件订阅: " . ($checked ? '开启' : '关闭') . "\n";
                                }),
                            
                            Builder::checkbox()
                                ->id('notificationsCheckbox')
                                ->text('启用推送通知')
                                ->checked(false)
                                ->onToggle(function($checked, $component) use ($state) {
                                    $state->set('selections.notifications', $checked);
                                    echo "推送通知: " . ($checked ? '开启' : '关闭') . "\n";
                                }),
                        ])
                    ]),
                
                // 数据显示
                Builder::group()
                    ->title('选择结果')
                    ->margined(true)
                    ->contains([
                        Builder::vbox()->padded(true)->contains([
                            Builder::label()->text('请在上方进行选择，结果将显示在这里')->id('resultLabel'),
                            Builder::button()
                                ->text('获取所有选择')
                                ->onClick(function($button, $state) {
                                    $selections = $state->get('selections');
                                    
                                    echo "=== 当前选择结果 ===\n";
                                    echo "国家: " . ($selections['country'] ?: '未选择') . "\n";
                                    echo "语言: " . ($selections['language'] ?: '未选择') . "\n";
                                    echo "性别: " . ($selections['gender'] ?: '未选择') . "\n";
                                    
                                    $interests = $selections['interests'];
                                    if (!empty($interests)) {
                                        echo "兴趣爱好: " . implode(', ', $interests) . "\n";
                                    } else {
                                        echo "兴趣爱好: 无\n";
                                    }
                                    
                                    echo "邮件订阅: " . ($selections['newsletter'] ? '是' : '否') . "\n";
                                    echo "推送通知: " . ($selections['notifications'] ? '是' : '否') . "\n";
                                    
                                    // 更新结果显示
                                    $resultLabel = StateManager::instance()->getComponent('resultLabel');
                                    if ($resultLabel) {
                                        $resultText = "国家: {$selections['country']} | ";
                                        $resultText .= "语言: {$selections['language']} | ";
                                        $resultText .= "性别: {$selections['gender']}";
                                        $resultLabel->setValue($resultText);
                                    }
                                }),
                        ])
                    ]),
                
                // 控制按钮
                Builder::hbox()->padded(true)->contains([
                    Builder::button()
                        ->text('重置')
                        ->onClick(function($button, $state) {
                            resetSelections($state);
                            echo "所有选择已重置\n";
                        }),
                    
                    Builder::button()
                        ->text('退出')
                        ->onClick(function($button) {
                            App::quit();
                        }),
                ]),
            ])
    ]);

// 更新兴趣爱好选择
function updateInterests($state, $interestsOptions, $index, $checked) {
    $currentInterests = $state->get('selections.interests') ?? [];
    
    if (!is_array($currentInterests)) {
        $currentInterests = [];
    }
    
    if ($checked) {
        if (!in_array($interestsOptions[$index], $currentInterests)) {
            $currentInterests[] = $interestsOptions[$index];
        }
    } else {
        $currentInterests = array_filter($currentInterests, function($interest) use ($interestsOptions, $index) {
            return $interest !== $interestsOptions[$index];
        });
    }
    
    $state->set('selections.interests', array_values($currentInterests));
    
    echo "兴趣爱好: " . implode(', ', $currentInterests) . "\n";
    
    $interestsLabel = StateManager::instance()->getComponent('interestsSelection');
    if ($interestsLabel) {
        $interestsText = !empty($currentInterests) ? "兴趣: " . implode(', ', $currentInterests) : "无兴趣选择";
        $interestsLabel->setValue($interestsText);
    }
}

// 重置所有选择
function resetSelections($state) {
    $state->set('selections', [
        'country' => '',
        'language' => '',
        'gender' => '',
        'interests' => [],
        'newsletter' => false,
        'notifications' => false
    ]);
    
    // 重置UI组件
    $components = [
        'countryCombo' => 0,
        'languageCombo' => 0,
        'genderRadio' => 0,
        'newsletterCheckbox' => true,
        'notificationsCheckbox' => false,
    ];
    
    foreach ($components as $componentId => $defaultValue) {
        $component = StateManager::instance()->getComponent($componentId);
        if ($component) {
            $component->setValue($defaultValue);
        }
    }
    
    // 重置复选框
    for ($i = 0; $i < 6; $i++) {
        $checkbox = StateManager::instance()->getComponent("interest_{$i}");
        if ($checkbox) {
            $checkbox->setValue(false);
        }
    }
    
    // 清空显示标签
    $labels = ['countrySelection', 'languageSelection', 'genderSelection', 'interestsSelection', 'resultLabel'];
    foreach ($labels as $labelId) {
        $label = StateManager::instance()->getComponent($labelId);
        if ($label) {
            $label->setValue('');
        }
    }
}

$app->show();