# HTML 标签别名支持

libuiBuilder 现在支持标准 HTML 标签的别名，使模板更符合 Web 标准。

## 支持的别名映射

| 标准 HTML 标签 | 映射到 | 说明 |
|----------------|--------|------|
| `<select>` | `combobox` | 下拉选择框 |
| `<progress>` | `progressbar` | 进度条 |
| `<hr>` | `separator` | 水平分隔线 |
| `<textarea>` | `multilineEntry` | 多行文本输入框 |

## 支持的 input 类型扩展

| input type | 映射到 | 说明 |
|------------|--------|------|
| `type="number"` | `spinbox` | 数字输入框 |
| `type="range"` | `slider` | 滑动条 |
| `type="password"` | `passwordEntry` | 密码输入框 |
| `type="multiline"` | `multilineEntry` | 多行文本（已弃用，使用 textarea） |

## 使用示例

### 下拉选择框
```html
<!-- 旧写法 -->
<combobox>
  <option>选项1</option>
  <option>选项2</option>
</combobox>

<!-- 新写法（标准 HTML） -->
<select>
  <option>选项1</option>
  <option>选项2</option>
</select>
```

### 进度条
```html
<!-- 旧写法 -->
<progressbar value="50" max="100"/>

<!-- 新写法（标准 HTML） -->
<progress value="50" max="100">50%</progress>
```

### 分隔线
```html
<!-- 旧写法 -->
<separator/>

<!-- 新写法（标准 HTML） -->
<hr/>
```

### 数字输入框
```html
<!-- 旧写法 -->
<spinbox min="0" max="100" value="50"/>

<!-- 新写法（标准 HTML） -->
<input type="number" min="0" max="100" value="50"/>
```

### 滑动条
```html
<!-- 旧写法 -->
<slider min="0" max="100" value="50"/>

<!-- 新写法（标准 HTML） -->
<input type="range" min="0" max="100" value="50"/>
```

### 多行文本
```html
<!-- 旧写法 -->
<input type="multiline"/>

<!-- 新写法（标准 HTML） -->
<textarea rows="4" cols="40" placeholder="请输入文本"></textarea>
```

## 向后兼容性

所有原有的 libuiBuilder 特有标签仍然支持，确保现有代码不会破坏：

- `window`, `grid`, `hbox`, `vbox`, `tab`
- `label`, `input`, `button`, `table`, `canvas`
- `checkbox`, `radio`, `combobox`, `spinbox`, `slider`, `progressbar`, `separator`

## 运行示例

```bash
# 运行标准 HTML 标签演示
php example/standard_html_demo.php
```

这个演示展示了如何使用标准 HTML 标签创建表单界面，包括文本输入、数字输入、滑动条、进度条、下拉选择和多行文本等组件。