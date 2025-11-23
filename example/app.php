<?php
require_once 'bootstrap.php';

use function Kingbes\Libui\View\view;

// 方式1：直接使用视图
view('main-window', [], [
    'fileNew' => function(){
        echo "新建文件\n";
    },
    'fileOpen'=> function () {
        echo "打开文件\n";
    },
    'toolSave' => function () {
        echo "保存文件\n";
    }
]);

// 方式2：通过控制器
//$userController = new UserController();
//$userController->showUserManagement();