<?php

use Kingbes\Libui\View\HtmlRenderer;

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir() . '/libui_test_' . uniqid();
    mkdir($this->tempDir);
});

afterEach(function () {
    if (isset($this->tempDir) && is_dir($this->tempDir)) {
        array_map('unlink', glob($this->tempDir . '/*'));
        rmdir($this->tempDir);
    }
});

describe('HtmlRenderer 扩展测试', function () {
    
    test('渲染所有容器组件', function () {
        $containers = [
            'vbox' => '<vbox padded="true"><label>VBox Content</label></vbox>',
            'hbox' => '<hbox padded="true"><label>HBox Content</label></hbox>',
            'grid' => '<grid padded="true"><label row="0" col="0">Grid Content</label></grid>',
            'tab' => '<tab><tabpage title="Tab 1"><label>Content 1</label></tabpage></tab>'
        ];
        
        foreach ($containers as $type => $content) {
            $html = <<<HTML
<!DOCTYPE html>
<ui>
  <window title="{$type} Test" size="400,300">
    {$content}
  </window>
</ui>
HTML;
            
            $filePath = $this->tempDir . "/{$type}_container.ui.html";
            file_put_contents($filePath, $html);
            
            $result = HtmlRenderer::render($filePath);
            expect($result)->toBeObject();
        }
    });
    
    test('渲染所有输入控件', function () {
        $inputs = [
            'text' => '<input type="text" placeholder="Text input"/>',
            'password' => '<input type="password" placeholder="Password input"/>',
            'multiline' => '<input type="multiline" placeholder="Multiline input"/>',
            'readonly' => '<input readonly="true" placeholder="Readonly input"/>'
        ];
        
        foreach ($inputs as $type => $content) {
            $html = <<<HTML
<!DOCTYPE html>
<ui>
  <window title="{$type} Input Test" size="400,300">
    <vbox>
      <label>{$type} Input:</label>
      {$content}
    </vbox>
  </window>
</ui>
HTML;
            
            $filePath = $this->tempDir . "/{$type}_input.ui.html";
            file_put_contents($filePath, $html);
            
            $result = HtmlRenderer::render($filePath);
            expect($result)->toBeObject();
        }
    });
    
    test('渲染所有选择控件', function () {
        $html = <<<HTML
<!DOCTYPE html>
<ui>
  <window title="Selection Controls Test" size="400,400">
    <vbox padded="true">
      <label>Checkboxes:</label>
      <checkbox id="check1" checked="true">Option 1</checkbox>
      <checkbox id="check2">Option 2</checkbox>
      <checkbox id="check3">Option 3</checkbox>
      
      <label>Radio Buttons:</label>
      <radio id="radio1" selected="0">
        <option>Choice A</option>
        <option>Choice B</option>
        <option>Choice C</option>
      </radio>
      
      <label>Combobox:</label>
      <combobox id="combo1" selected="0">
        <option>Select...</option>
        <option>Option 1</option>
        <option>Option 2</option>
      </combobox>
    </vbox>
  </window>
</ui>
HTML;
        
        $filePath = $this->tempDir . '/selection_controls.ui.html';
        file_put_contents($filePath, $html);
        
        $result = HtmlRenderer::render($filePath);
        expect($result)->toBeObject();
    });
    
    test('渲染所有数值控件', function () {
        $html = <<<HTML
<!DOCTYPE html>
<ui>
  <window title="Numeric Controls Test" size="400,300">
    <grid padded="true">
      <label row="0" col="0">Spinbox:</label>
      <spinbox row="0" col="1" min="0" max="100" value="50"/>
      
      <label row="1" col="0">Slider:</label>
      <slider row="1" col="1" min="0" max="100" value="30"/>
      
      <label row="2" col="0">Progress:</label>
      <progressbar row="2" col="1" value="75"/>
    </grid>
  </window>
</ui>
HTML;
        
        $filePath = $this->tempDir . '/numeric_controls.ui.html';
        file_put_contents($filePath, $html);
        
        $result = HtmlRenderer::render($filePath);
        expect($result)->toBeObject();
    });
    
    test('渲染复杂 Grid 布局', function () {
        $html = <<<HTML
<!DOCTYPE html>
<ui>
  <window title="Complex Grid Layout" size="600,400">
    <grid padded="true">
      <!-- 标题行，跨3列 -->
      <label row="0" col="0" colspan="3" align="center">Application Settings</label>
      
      <!-- 用户设置区域 -->
      <label row="1" col="0" align="end,center">Username:</label>
      <input row="1" col="1" id="username" expand="horizontal"/>
      <button row="1" col="2">Check</button>
      
      <label row="2" col="0" align="end,center">Email:</label>
      <input row="2" col="1" id="email" type="email" expand="horizontal"/>
      <button row="2" col="2">Verify</button>
      
      <!-- 主题设置区域，跨2列 -->
      <label row="3" col="0" align="end,center">Theme:</label>
      <combobox row="3" col="1" colspan="2" selected="1" expand="horizontal">
        <option>Light</option>
        <option>Dark</option>
        <option>Auto</option>
      </combobox>
      
      <!-- 通知设置，跨3列 -->
      <checkbox row="4" col="0" colspan="3" checked="true">Enable notifications</checkbox>
      
      <!-- 按钮区域 -->
      <button row="5" col="0" align="center">Cancel</button>
      <button row="5" col="1" align="center">Reset</button>
      <button row="5" col="2" align="center">Save</button>
    </grid>
  </window>
</ui>
HTML;
        
        $filePath = $this->tempDir . '/complex_grid.ui.html';
        file_put_contents($filePath, $html);
        
        $result = HtmlRenderer::render($filePath);
        expect($result)->toBeObject();
    });
    
    test('渲染带有所有事件类型的窗口', function () {
        $html = <<<HTML
<!DOCTYPE html>
<ui>
  <window title="Events Test" size="500,400">
    <vbox padded="true">
      <button id="btn1" onclick="handleClick1">Click Event</button>
      <input id="input1" onchange="handleChange1">Change Event</input>
      <radio id="radio1" onselected="handleSelect1">
        <option>A</option>
        <option>B</option>
      </radio>
      <checkbox id="check1" ontoggled="handleToggle1">Toggle Event</checkbox>
      <slider id="slider1" onchange="handleSliderChange1">Slider Event</slider>
    </vbox>
  </window>
</ui>
HTML;
        
        $filePath = $this->tempDir . '/events_test.ui.html';
        file_put_contents($filePath, $html);
        
        $handlers = [
            'handleClick1' => function() { return 'clicked'; },
            'handleChange1' => function($value) { return $value; },
            'handleSelect1' => function($index) { return $index; },
            'handleToggle1' => function($checked) { return $checked; },
            'handleSliderChange1' => function($value) { return $value; }
        ];
        
        $result = HtmlRenderer::render($filePath, $handlers);
        expect($result)->toBeObject();
    });
    
    test('渲染带有数据绑定的复杂表单', function () {
        $html = <<<HTML
<!DOCTYPE html>
<ui>
  <window title="Data Binding Test" size="500,500">
    <grid padded="true">
      <label row="0" col="0">Name:</label>
      <input row="0" col="1" id="name" bind="userName" expand="horizontal"/>
      
      <label row="1" col="0">Email:</label>
      <input row="1" col="1" id="email" bind="userEmail" type="email" expand="horizontal"/>
      
      <label row="2" col="0">Age:</label>
      <spinbox row="2" col="1" id="age" bind="userAge" min="0" max="120"/>
      
      <label row="3" col="0">City:</label>
      <combobox row="3" col="1" id="city" bind="userCity" expand="horizontal">
        <option>Beijing</option>
        <option>Shanghai</option>
        <option>Guangzhou</option>
      </combobox>
      
      <checkbox row="4" col="0" colspan="2" id="newsletter" bind="subscribeNewsletter">
        Subscribe to newsletter
      </checkbox>
      
      <label row="5" col="0" colspan="2">Status: {{formStatus}}</label>
    </grid>
  </window>
</ui>
HTML;
        
        $filePath = $this->tempDir . '/data_binding.ui.html';
        file_put_contents($filePath, $html);
        
        $variables = [
            'formStatus' => 'Ready to submit'
        ];
        
        $result = HtmlRenderer::render($filePath, [], $variables);
        expect($result)->toBeObject();
    });
    
    test('渲染模板系统完整功能', function () {
        $html = <<<HTML
<!DOCTYPE html>
<ui>
  <template id="form-field">
    <label row="{{row}}" col="0" align="end,center">{{label}}:</label>
    <input row="{{row}}" col="1" id="{{id}}" bind="{{bind}}" placeholder="{{placeholder}}" expand="horizontal"/>
  </template>
  
  <template id="button-row">
    <button row="{{row}}" col="0" onclick="{{cancelHandler}}">{{cancelText}}</button>
    <button row="{{row}}" col="1" onclick="{{submitHandler}}" align="end">{{submitText}}</button>
  </template>
  
  <window title="Template System Test" size="500,400">
    <grid padded="true">
      <use template="form-field"/>
      <use template="form-field"/>
      <use template="button-row"/>
    </grid>
  </window>
</ui>
HTML;
        
        $filePath = $this->tempDir . '/template_system.ui.html';
        file_put_contents($filePath, $html);
        
        $variables = [
            'row' => 0,
            'label' => 'Test Field',
            'id' => 'testField',
            'bind' => 'testValue',
            'placeholder' => 'Enter test value'
        ];
        
        $result = HtmlRenderer::render($filePath, [], $variables);
        expect($result)->toBeObject();
    });
    
    test('渲染所有辅助组件', function () {
        $html = <<<HTML
<!DOCTYPE html>
<ui>
  <window title="Helper Components Test" size="500,600">
    <vbox padded="true">
      <label>Separators:</label>
      <separator orientation="horizontal"/>
      <label>Content between separators</label>
      <separator orientation="horizontal"/>
      
      <label>Table:</label>
      <table>
        <column>Name</column>
        <column>Age</column>
        <column>City</column>
      </table>
      
      <label>Canvas:</label>
      <canvas/>
      
      <label>Progress Bars:</label>
      <progressbar value="25"/>
      <progressbar value="50"/>
      <progressbar value="75"/>
      <progressbar value="100"/>
    </vbox>
  </window>
</ui>
HTML;
        
        $filePath = $this->tempDir . '/helper_components.ui.html';
        file_put_contents($filePath, $html);
        
        $result = HtmlRenderer::render($filePath);
        expect($result)->toBeObject();
    });
    
    test('处理窗口的各种属性组合', function () {
        $windowConfigs = [
            ['title' => 'Basic Window', 'size' => '400,300'],
            ['title' => 'Centered Window', 'size' => '600,400', 'centered' => 'true'],
            ['title' => 'Margined Window', 'size' => '500,350', 'margined' => 'true'],
            ['title' => 'Full Window', 'size' => '800,600', 'centered' => 'true', 'margined' => 'true']
        ];
        
        foreach ($windowConfigs as $index => $config) {
            $attributes = '';
            foreach ($config as $key => $value) {
                $attributes .= " {$key}=\"{$value}\"";
            }
            
            $html = <<<HTML
<!DOCTYPE html>
<ui>
  <window{$attributes}>
    <label>Window {$index}</label>
  </window>
</ui>
HTML;
            
            $filePath = $this->tempDir . "/window_config_{$index}.ui.html";
            file_put_contents($filePath, $html);
            
            $result = HtmlRenderer::render($filePath);
            expect($result)->toBeObject();
        }
    });
});