<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

// 初始化应用
App::init();

// 初始化状态管理器
$state = StateManager::instance();

// 设置默认状态
$state->set('files', []);
$state->set('outputFormat', 'srt');
$state->set('selectedFile', null);

// 定义事件处理器
$handlers = [
    'addFile' => function($button, $state) {
        echo "添加文件功能\n";
        // 这里可以添加文件选择对话框逻辑
    },
    
    'deleteSelected' => function($button, $state) {
        $selectedFile = $state->get('selectedFile');
        if ($selectedFile) {
            $files = $state->get('files');
            $files = array_filter($files, function($file) use ($selectedFile) {
                return $file['id'] !== $selectedFile['id'];
            });
            $state->set('files', $files);
            $state->set('selectedFile', null);
            echo "已删除选中文件\n";
        }
    },
    
    'start' => function($button, $state) {
        $files = $state->get('files');
        $format = $state->get('outputFormat');
        
        if (empty($files)) {
            echo "请先添加文件\n";
            return;
        }
        
        echo "开始处理 " . count($files) . " 个文件，格式：{$format}\n";
        // 这里可以添加实际的文件处理逻辑
    },
    
    'about' => function($button, $state) {
        echo "STS-Bcut v1.0.4\n";
        echo "一个简单易用的字幕文件处理工具\n";
    },
    
    'settings' => function($button, $state) {
        echo "打开设置面板\n";
        // 这里可以添加设置对话框逻辑
    }
];

// 渲染HTML界面
$app = HtmlRenderer::render(__DIR__.'/views/sts_bcut.ui.html', $handlers);

// 显示窗口
$app->show();
