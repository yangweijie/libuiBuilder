<?php

use Kingbes\Libui\View\HtmlRenderer;

beforeEach(function () {
    // 创建测试用的临时目录
    $this->tempDir = sys_get_temp_dir() . '/libui_test_' . uniqid();
    mkdir($this->tempDir);
});

afterEach(function () {
    // 清理临时文件
    if (isset($this->tempDir) && is_dir($this->tempDir)) {
        array_map('unlink', glob($this->tempDir . '/*'));
        rmdir($this->tempDir);
    }
});

test('HtmlRenderer 基础渲染', function () {
    $html = <<<HTML
<!DOCTYPE html>
<ui>
  <window title="Test Window" size="400,300">
    <label>Hello World</label>
  </window>
</ui>
HTML;
    
    $filePath = $this->tempDir . '/test.ui.html';
    file_put_contents($filePath, $html);
    
    $result = HtmlRenderer::render($filePath);
    
    expect($result)->toBeObject();
});

test('HtmlRenderer 中文支持', function () {
    $html = <<<HTML
<!DOCTYPE html>
<ui>
  <window title="中文测试" size="400,300">
    <label>这是一个中文标签</label>
    <button>中文按钮</button>
  </window>
</ui>
HTML;
    
    $filePath = $this->tempDir . '/chinese_test.ui.html';
    file_put_contents($filePath, $html);
    
    $result = HtmlRenderer::render($filePath);
    
    expect($result)->toBeObject();
});

test('HtmlRenderer 事件绑定', function () {
    $html = <<<HTML
<!DOCTYPE html>
<ui>
  <window title="Event Test" size="400,300">
    <button id="testBtn" onclick="handleClick">Click Me</button>
  </window>
</ui>
HTML;
    
    $filePath = $this->tempDir . '/event_test.ui.html';
    file_put_contents($filePath, $html);
    
    $clicked = false;
    $handlers = [
        'handleClick' => function() use (&$clicked) {
            $clicked = true;
        }
    ];
    
    $result = HtmlRenderer::render($filePath, $handlers);
    
    expect($result)->toBeObject();
    expect($clicked)->toBeFalse(); // 事件还没有触发
});

test('HtmlRenderer 模板变量', function () {
    $html = <<<HTML
<!DOCTYPE html>
<ui>
  <window title="{{windowTitle}}" size="400,300">
    <label>Welcome, {{username}}!</label>
  </window>
</ui>
HTML;
    
    $filePath = $this->tempDir . '/variables_test.ui.html';
    file_put_contents($filePath, $html);
    
    $variables = [
        'windowTitle' => 'My App',
        'username' => 'John'
    ];
    
    $result = HtmlRenderer::render($filePath, [], $variables);
    
    expect($result)->toBeObject();
});

test('HtmlRenderer 错误处理', function () {
    expect(fn() => HtmlRenderer::render('/nonexistent/file.html'))
        ->toThrow(Exception::class, 'HTML template file not found');
});