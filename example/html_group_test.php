<?php
use Kingbes\Libui\App;
use Kingbes\Libui\View\HtmlRenderer;
use Kingbes\Libui\View\State\StateManager;

require_once __DIR__ . '/../vendor/autoload.php';

App::init();

$state = StateManager::instance();
$state->set('username', '');
$state->set('password', '');

$handlers = [
    'handleLogin' => function($button, $state) {
        echo "登录: " . $state->get('username') . "\n";
    },
    
    'handleReset' => function($button, $state) {
        $state->update([
            'username' => '',
            'password' => ''
        ]);
    }
];

$app = HtmlRenderer::render('example/views/group_test.ui.html', $handlers);
$app->show();