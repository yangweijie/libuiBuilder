<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helper.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

// 初始化状态管理器
$stateManager = StateManager::instance();

// 初始联系人数据
$contactsOrigin = [
    ['Lisa Sky', 'lisa@sky.com', '720-523-4329', 'Denver', 'CO'],
    ['Jordan Biggins', 'jordan@biggins.', '617-528-5399', 'Boston', 'MA'],
    ['Mary Glass', 'mary@glass.con', '847-589-8788', 'Elk Grove Village', 'IL'],
    ['Darren McGrath', 'darren@mcgrat', '206-539-9283', 'Seattle', 'WA'],
    ['Melody Hanheir', 'melody@hanhei', '213-493-8274', 'Los Angeles', 'CA'],
];

$stateManager->set('contacts', $contactsOrigin);
$stateManager->set('filteredContacts', $contactsOrigin);
$stateManager->set('searchKeyword', '');

// 事件处理器
$handlers = [
    'saveContact' => function() use ($stateManager) {
        $row = [];
        $allFilled = true;
        $fieldNames = ['Name', 'Email', 'Phone', 'City', 'State'];
        
        foreach ($fieldNames as $field) {
            $value = $stateManager->get("form.{$field}", '');
            if (trim($value) === '') {
                $allFilled = false;
            }
            $row[] = $value;
        }
        
        if ($allFilled) {
            // 获取当前联系人数据并添加新联系人
            $contacts = $stateManager->get('contacts');
            $contacts[] = $row;
            $stateManager->set('contacts', $contacts);
            $stateManager->set('filteredContacts', $contacts); // 更新过滤后的数据
            
            // 清空表单
            foreach ($fieldNames as $field) {
                $stateManager->set("form.{$field}", '');
            }
            
            echo "成功添加联系人: " . $row[0] . "\n";
        }
    },
    
    'searchContacts' => function() use ($stateManager) {
        $keyword = trim($stateManager->get('searchKeyword', ''));
        $contactsOrigin = $stateManager->get('contacts');
        $filteredContacts = [];
        
        if ($keyword === '') {
            $filteredContacts = $contactsOrigin;
        } else {
            foreach ($contactsOrigin as $row) {
                $found = false;
                foreach ($row as $cell) {
                    if (strpos(strtolower($cell), strtolower($keyword)) !== false) {
                        $found = true;
                        break;
                    }
                }
                if ($found) {
                    $filteredContacts[] = $row;
                }
            }
        }
        
        $stateManager->set('filteredContacts', $filteredContacts);
        echo "搜索完成，找到 " . count($filteredContacts) . " 条记录\n";
    }
];

// 创建应用界面
$app = Builder::window()
    ->title('Contacts')
    ->size(600, 600)
    ->margined(true)
    ->onClosing(function($window) {
        App::quit();
        return 1;
    })
    ->contains([
        Builder::vbox()
            ->padded(true)
            ->contains([
                // 表单区域
                Builder::vbox()
                    ->id('formArea')
                    ->padded(true)
                    ->contains([
                        // 创建表单字段
                        Builder::label()->text('Name')->id('nameLabel'),
                        Builder::entry()
                            ->id('formName')
                            ->bind('form.Name')
                            ->placeholder('输入姓名'),
                        Builder::label()->text('Email')->id('emailLabel'),
                        Builder::entry()
                            ->id('formEmail')
                            ->bind('form.Email')
                            ->placeholder('输入邮箱'),
                        Builder::label()->text('Phone')->id('phoneLabel'),
                        Builder::entry()
                            ->id('formPhone')
                            ->bind('form.Phone')
                            ->placeholder('输入电话'),
                        Builder::label()->text('City')->id('cityLabel'),
                        Builder::entry()
                            ->id('formCity')
                            ->bind('form.City')
                            ->placeholder('输入城市'),
                        Builder::label()->text('State')->id('stateLabel'),
                        Builder::entry()
                            ->id('formState')
                            ->bind('form.State')
                            ->placeholder('输入州'),
                        Builder::button()
                            ->text('Save Contact')
                            ->id('saveBtn')
                            ->onClick(function($button) use ($handlers) {
                                $handlers['saveContact']();
                            }),
                        Builder::separator(),
                    ]),

                // 搜索区域
                Builder::hbox()
                    ->padded(true)
                    ->contains([
                        Builder::entry()
                            ->id('searchEntry')
                            ->bind('searchKeyword')
                            ->placeholder('搜索联系人...'),
                        Builder::button()
                            ->text('Search')
                            ->id('searchBtn')
                            ->onClick(function($button) use ($handlers) {
                                $handlers['searchContacts']();
                            }),
                    ]),
                Builder::separator(),

                // 表格组件
                Builder::table()
                    ->id('contactTable')
                    ->columns([
                        ['title' => 'Name', 'type' => 'text'],
                        ['title' => 'Email', 'type' => 'text'],
                        ['title' => 'Phone', 'type' => 'text'],
                        ['title' => 'City', 'type' => 'text'],
                        ['title' => 'State', 'type' => 'text']
                    ])
                    ->bind('filteredContacts')
            ])
    ]);

// 构建界面
$app->build();

// 显示应用
$app->show();
App::main();
