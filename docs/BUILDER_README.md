# libuiBuilder - 链式构建器模式文档

基于 kingbes/libui 的链式构建器模式 GUI 开发框架，提供现代化、易用的 PHP GUI 开发体验。

## 🚀 快速开始

### 基础用法

```php
use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\State\StateManager;

// 初始化
App::init();
Builder::setStateManager(StateManager::instance());

// 创建窗口
Builder::window()
    ->title('我的应用')
    ->size(400, 300)
    ->margined(true)
    ->contains(
        Builder::vbox()
            ->padded(true)
            ->contains([
                Builder::label()->text('Hello World!'),
                Builder::button()
                    ->text('点击我')
                    ->onClick(function() {
                        echo "按钮被点击！\n";
                    })
            ])
    )
    ->show();

App::main();
```

## 📦 核心组件

### 1. Builder (工厂类)

提供静态方法创建所有组件构建器：

```php
Builder::window()      // 窗口
Builder::button()      // 按钮
Builder::label()       // 标签
Builder::entry()       // 输入框
Builder::hbox()        // 水平盒子
Builder::vbox()        // 垂直盒子
Builder::grid()        // 网格布局
Builder::tab()         // 标签页
Builder::table()       // 表格
Builder::checkbox()    // 复选框
Builder::combobox()    // 组合框
Builder::separator()   // 分隔线
Builder::progress()    // 进度条
Builder::slider()      // 滑块
Builder::spinbox()     // 数字输入
Builder::group()       // 组容器
```

### 2. WindowBuilder (窗口构建器)

```php
Builder::window()
    ->title('窗口标题')           // 设置标题
    ->size(800, 600)            // 设置大小
    ->resizable(true)           // 是否可调整大小
    ->margined(true)            // 是否有边距
    ->menubar(false)            // 是否有菜单条
    ->onClosing(function() {    // 关闭事件
        App::quit();
        return 0;
    })
    ->contains($child)          // 设置子组件
    ->show()                    // 显示窗口
```

### 3. 按钮构建器

```php
Builder::button()
    ->id('myButton')            // 设置ID
    ->text('点击我')             // 设置文本
    ->onClick(function($btn, $state) {
        echo "按钮被点击！\n";
        // 访问状态管理器
        $state->set('clicked', true);
    })
```

### 4. 输入框构建器

```php
Builder::entry()
    ->id('username')
    ->placeholder('请输入用户名')  // 占位符
    ->bind('username')           // 绑定到状态
    ->password()                 // 密码框
    ->search()                   // 搜索框
    ->readOnly(false)            // 只读模式
    ->onChange(function($value, $component, $state) {
        echo "输入值: {$value}\n";
    })
```

### 5. 网格布局构建器

```php
Builder::grid()
    ->columns(2)                 // 2列网格
    ->padded(true)               // 内边距
    ->append(Builder::label()->text('用户名:'), 0, 0)
    ->append(Builder::entry(), 0, 1)
    ->append(Builder::label()->text('密码:'), 1, 0)
    ->append(Builder::entry()->password(), 1, 1)
```

**快速表单创建：**

```php
Builder::grid()
    ->columns(2)
    ->padded(true)
    ->form([
        [
            'label' => Builder::label()->text('用户名:'),
            'control' => Builder::entry()->bind('username')
        ],
        [
            'label' => Builder::label()->text('密码:'),
            'control' => Builder::entry()->password()->bind('password')
        ]
    ])
```

### 6. 盒子容器构建器

```php
// 水平盒子
Builder::hbox()
    ->padded(true)
    ->contains([
        Builder::button()->text('确定'),
        Builder::button()->text('取消')
    ])

// 垂直盒子
Builder::vbox()
    ->padded(true)
    ->contains([
        Builder::label()->text('标题'),
        Builder::separator(),
        Builder::label()->text('内容')
    ])
```

### 7. 标签页构建器

```php
Builder::tab()
    ->tabs([
        '标签页1' => Builder::label()->text('内容1'),
        '标签页2' => Builder::label()->text('内容2'),
    ])
    ->onTabSelected(function($index, $tab, $state) {
        echo "切换到标签页 {$index}\n";
    })
```

### 8. 表格构建器

```php
Builder::table()
    ->id('userTable')
    ->columns(['ID', '姓名', '邮箱'])
    ->data([
        [1, 'Alice', 'alice@example.com'],
        [2, 'Bob', 'bob@example.com']
    ])
    ->onRowSelected(function($row, $component) {
        echo "选中第 {$row} 行\n";
    })
```

### 9. 选择控件

```php
// 复选框
Builder::checkbox()
    ->text('记住我')
    ->bind('remember')
    ->onChange(function($checked, $component, $state) {
        echo "状态: " . ($checked ? '已选中' : '未选中') . "\n";
    })

// 组合框
Builder::combobox()
    ->items(['选项1', '选项2', '选项3'])
    ->selected(0)
    ->bind('selectedOption')
    ->onChange(function($index, $value, $component, $state) {
        echo "选中: {$value} (索引: {$index})\n";
    })
```

### 10. 进度控件

```php
// 滑块
Builder::slider()
    ->range(0, 100)
    ->value(50)
    ->bind('progress')
    ->onChange(function($value, $component, $state) {
        echo "滑块值: {$value}\n";
    })

// 进度条
Builder::progress()
    ->value(50)

// 数字输入
Builder::spinbox()
    ->range(0, 100)
    ->value(50)
    ->bind('count')
```

### 11. 组容器

```php
Builder::group()
    ->title('用户信息')
    ->margined(true)
    ->contains(
        Builder::vbox()
            ->padded(true)
            ->contains([
                Builder::label()->text('用户名: admin'),
                Builder::label()->text('角色: 管理员')
            ])
    )
```

## 🎨 状态管理系统

### 基本用法

```php
use Kingbes\Libui\View\State\StateManager;

// 获取单例
$state = StateManager::instance();

// 设置状态
$state->set('username', 'Alice');
$state->set('count', 0);

// 获取状态
$username = $state->get('username');
$count = $state->get('count', 0); // 带默认值

// 检查状态
if ($state->has('username')) {
    echo "用户名已设置\n";
}

// 批量更新
$state->update([
    'username' => 'Bob',
    'count' => 10
]);

// 删除状态
$state->delete('count');

// 获取所有状态
$all = $state->getAll();
```

### 状态监听

```php
// 监听状态变化
$state->watch('username', function($newValue, $oldValue) {
    echo "用户名从 {$oldValue} 变为 {$newValue}\n";
});

// 监听多个状态
$state->watch('count', function($newValue) {
    echo "计数: {$newValue}\n";
});
```

### 组件状态绑定

```php
// 设置全局状态管理器
Builder::setStateManager($state);

// 绑定到状态
Builder::entry()
    ->id('usernameInput')
    ->bind('username')  // 自动同步到状态
    ->onChange(function($value, $component, $stateManager) {
        // 输入改变时会自动更新状态
    });

// 在其他地方访问状态
$state->watch('username', function($newValue) {
    echo "用户名已更新: {$newValue}\n";
});

// 通过ID获取组件并更新
$component = $state->getComponent('usernameInput');
if ($component) {
    $component->setValue('新值');
}
```

## 🎯 完整示例

### 登录表单

```php
use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

$state = StateManager::instance();
Builder::setStateManager($state);

$app = Builder::window()
    ->title('登录')
    ->size(400, 200)
    ->margined(true)
    ->contains(
        Builder::vbox()
            ->padded(true)
            ->contains([
                Builder::grid()
                    ->columns(2)
                    ->padded(true)
                    ->form([
                        [
                            'label' => Builder::label()->text('用户名:'),
                            'control' => Builder::entry()
                                ->bind('username')
                                ->placeholder('请输入用户名')
                        ],
                        [
                            'label' => Builder::label()->text('密码:'),
                            'control' => Builder::entry()
                                ->password()
                                ->bind('password')
                                ->placeholder('请输入密码')
                        ]
                    ]),
                
                Builder::hbox()
                    ->padded(true)
                    ->contains([
                        Builder::button()
                            ->text('登录')
                            ->onClick(function($btn, $state) {
                                $username = $state->get('username');
                                $password = $state->get('password');
                                
                                if (empty($username) || empty($password)) {
                                    echo "用户名和密码不能为空！\n";
                                    return;
                                }
                                
                                echo "登录成功！用户名: {$username}\n";
                            }),
                        
                        Builder::button()
                            ->text('清空')
                            ->onClick(function($btn, $state) {
                                $state->update([
                                    'username' => '',
                                    'password' => ''
                                ]);
                                
                                // 手动更新UI
                                $state->getComponent('usernameInput')?->setValue('');
                                $state->getComponent('passwordInput')?->setValue('');
                            })
                    ])
            ])
    )
    ->show();

App::main();
```

### 计数器应用

```php
use Kingbes\Libui\App;
use Kingbes\Libui\View\Builder\Builder;
use Kingbes\Libui\View\State\StateManager;

App::init();

$state = StateManager::instance();
$state->set('count', 0);
Builder::setStateManager($state);

$app = Builder::window()
    ->title('计数器')
    ->size(300, 150)
    ->margined(true)
    ->contains(
        Builder::vbox()
            ->padded(true)
            ->contains([
                Builder::label()
                    ->id('countLabel')
                    ->text('当前计数: 0')
                    ->align('center'),
                
                Builder::hbox()
                    ->padded(true)
                    ->contains([
                        Builder::button()
                            ->text('增加 +1')
                            ->onClick(function($btn, $state) {
                                $count = $state->get('count', 0) + 1;
                                $state->set('count', $count);
                                
                                // 更新显示
                                $label = $state->getComponent('countLabel');
                                if ($label) {
                                    $label->setText("当前计数: {$count}");
                                }
                            }),
                        
                        Builder::button()
                            ->text('重置')
                            ->onClick(function($btn, $state) {
                                $state->set('count', 0);
                                $label = $state->getComponent('countLabel');
                                if ($label) {
                                    $label->setText("当前计数: 0");
                                }
                            })
                    ])
            ])
    )
    ->show();

// 监听状态变化
$state->watch('count', function($newValue) {
    echo "计数更新: {$newValue}\n";
});

App::main();
```

## 🔧 高级特性

### 1. 动态更新组件

```php
// 标签
$label = Builder::label()->id('myLabel')->text('初始文本');
$label->setText('新文本');

// 按钮
$button = Builder::button()->id('myButton')->text('点击');
$button->setText('已点击');

// 输入框
$entry = Builder::entry()->id('myEntry');
$entry->setValue('新值');

// 进度条
$progress = Builder::progress()->id('myProgress');
$progress->setValue(75);
```

### 2. 组件间通信

```php
// 通过状态管理器
$state = StateManager::instance();

// 组件A更新状态
Builder::button()
    ->text('更新数据')
    ->onClick(function($btn, $state) {
        $state->set('data', '新数据');
    });

// 组件B监听变化
$state->watch('data', function($newValue) {
    echo "数据已更新: {$newValue}\n";
});
```

### 3. 条件渲染

```php
$state = StateManager::instance();
$state->set('showAdvanced', false);

// 创建容器
$container = Builder::vbox()->padded(true);

// 根据状态添加组件
if ($state->get('showAdvanced')) {
    $container->append(Builder::label()->text('高级选项'));
}

// 切换状态时重建（需要手动处理）
Builder::button()
    ->text('显示高级选项')
    ->onClick(function($btn, $state) {
        $state->set('showAdvanced', true);
        echo "请重建窗口以显示高级选项\n";
    });
```

## 📝 最佳实践

### 1. 组件 ID 管理

```php
// 为需要访问的组件设置ID
Builder::entry()
    ->id('usernameInput')  // 用于后续访问
    ->bind('username');

// 通过状态管理器访问
$component = $state->getComponent('usernameInput');
```

### 2. 事件处理

```php
// 简单事件
Builder::button()
    ->onClick(function($btn) {
        echo "点击\n";
    });

// 访问状态管理器
Builder::button()
    ->onClick(function($btn, $state) {
        $value = $state->get('key');
        $state->set('key', $newValue);
    });

// 输入框变化
Builder::entry()
    ->onChange(function($value, $component, $state) {
        echo "输入: {$value}\n";
        $state->set('input', $value);
    });
```

### 3. 状态绑定

```php
// 自动同步
Builder::entry()
    ->bind('username')  // 自动更新状态

// 手动同步
$state->watch('username', function($newValue) {
    // 处理变化
});
```

### 4. 错误处理

```php
Builder::button()
    ->onClick(function($btn, $state) {
        try {
            $value = $state->get('username');
            if (empty($value)) {
                throw new \Exception('用户名不能为空');
            }
            // 处理逻辑
        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . "\n";
        }
    });
```

## 🎨 组件类型总结

| 组件 | 创建方法 | 主要方法 |
|------|---------|---------|
| 窗口 | `Builder::window()` | `title()`, `size()`, `contains()`, `show()` |
| 按钮 | `Builder::button()` | `text()`, `onClick()` |
| 标签 | `Builder::label()` | `text()`, `align()` |
| 输入框 | `Builder::entry()` | `placeholder()`, `bind()`, `password()`, `onChange()` |
| 水平盒子 | `Builder::hbox()` | `padded()`, `contains()` |
| 垂直盒子 | `Builder::vbox()` | `padded()`, `contains()` |
| 网格 | `Builder::grid()` | `columns()`, `padded()`, `append()`, `form()` |
| 标签页 | `Builder::tab()` | `tabs()`, `onTabSelected()` |
| 表格 | `Builder::table()` | `columns()`, `data()`, `onRowSelected()` |
| 复选框 | `Builder::checkbox()` | `text()`, `bind()`, `onChange()` |
| 组合框 | `Builder::combobox()` | `items()`, `selected()`, `bind()`, `onChange()` |
| 分隔线 | `Builder::separator()` | 无配置方法 |
| 进度条 | `Builder::progress()` | `value()` |
| 滑块 | `Builder::slider()` | `range()`, `value()`, `bind()`, `onChange()` |
| 数字输入 | `Builder::spinbox()` | `range()`, `value()`, `bind()`, `onChange()` |
| 组容器 | `Builder::group()` | `title()`, `margined()`, `contains()` |

## 📚 更多示例

查看 `example/04_advanced/builder_example.php` 获取完整示例代码。

## 🔄 与 kingbes/libui 的关系

本框架是对 kingbes/libui 的高层封装：

- **kingbes/libui**: 提供底层 FFI 绑定，直接操作 libui
- **libuiBuilder**: 提供链式构建器模式，简化开发

你可以混合使用两者：
```php
// 使用构建器创建
$builder = Builder::button()->text('点击');
$handle = $builder->build();

// 使用 kingbes/libui 操作
\Kingbes\Libui\Button::setText($handle, '新文本');
```

## 🎯 特性对比

| 特性 | 原生 kingbes/libui | libuiBuilder |
|------|-------------------|--------------|
| 语法 | 静态方法调用 | 链式调用 |
| 状态管理 | 手动管理 | 自动绑定 |
| 组件引用 | 需要保存句柄 | 通过ID访问 |
| 事件处理 | 回调函数 | 链式方法 |
| 代码量 | 较多 | 简洁 |

## 🚨 注意事项

1. **必须先初始化**: `App::init()` 必须在创建组件前调用
2. **状态管理器**: 使用 `Builder::setStateManager()` 设置全局状态管理器
3. **组件ID**: 只有设置了ID的组件才能通过状态管理器访问
4. **事件返回值**: `onClosing` 事件应返回 0 允许关闭，返回 1 阻止关闭
5. **主循环**: 必须调用 `App::main()` 启动事件循环

## 🔗 相关资源

- [kingbes/libui 文档](https://github.com/kingbes/libui)
- [libui 原生文档](https://github.com/andlabs/libui)
- [项目示例](../example/04_advanced/builder_example.php)