<?php

namespace Kingbes\Libui\View\State;

use BadMethodCallException;
use Kingbes\Libui\View\ComponentBuilder;

/**
 * 组件操作器 - 提供统一的组件访问和操作接口
 */
class ComponentOperator
{
    private array $components;
    private StateManager $state;

    public function __construct(array $components, StateManager $state)
    {
        $this->components = $components;
        $this->state = $state;
    }

    /**
     * 通过ID获取组件
     */
    public function get(string $id): ?ComponentBuilder
    {
        return $this->components[$id] ?? null;
    }

    /**
     * 获取组件的值
     */
    public function getValue(string $id)
    {
        $component = $this->get($id);
        return $component ? $component->getValue() : null;
    }

    /**
     * 设置组件的值
     */
    public function setValue(string $id, $value): self
    {
        $component = $this->get($id);
        if ($component) {
            $component->setValue($value);
        }
        return $this;
    }

    /**
     * 获取组件的文本（适用于label、button等）
     */
    public function getText(string $id): ?string
    {
        $component = $this->get($id);
        return $component ? $component->getConfig('text') : null;
    }

    /**
     * 设置组件的文本
     */
    public function setText(string $id, string $text): self
    {
        $component = $this->get($id);
        if ($component) {
            $component->setConfig('text', $text);
            // 如果组件已经构建，更新原生控件
            if (method_exists($component, 'updateText')) {
                $component->updateText($text);
            }
        }
        return $this;
    }

    /**
     * 启用/禁用组件
     */
    public function setEnabled(string $id, bool $enabled): self
    {
        $component = $this->get($id);
        if ($component) {
            $component->setConfig('enabled', $enabled);
        }
        return $this;
    }

    /**
     * 显示/隐藏组件
     */
    public function setVisible(string $id, bool $visible): self
    {
        $component = $this->get($id);
        if ($component) {
            $component->setConfig('visible', $visible);
        }
        return $this;
    }

    /**
     * 获取复选框的选中状态
     */
    public function isChecked(string $id): bool
    {
        return (bool)$this->getValue($id);
    }

    /**
     * 设置复选框的选中状态
     */
    public function setChecked(string $id, bool $checked): self
    {
        return $this->setValue($id, $checked);
    }

    /**
     * 获取下拉框的选中项
     */
    public function getSelectedIndex(string $id): int
    {
        $component = $this->get($id);
        if ($component) {
            $value = $component->getValue();
            return is_array($value) ? ($value['index'] ?? -1) : -1;
        }
        return -1;
    }

    /**
     * 设置下拉框的选中项
     */
    public function setSelectedIndex(string $id, int $index): self
    {
        return $this->setValue($id, $index);
    }

    /**
     * 获取表格的选中行
     */
    public function getSelectedRow(string $id): int
    {
        $component = $this->get($id);
        return $component ? ($component->getConfig('selectedRow') ?? -1) : -1;
    }

    /**
     * 清空输入框
     */
    public function clear(string $id): self
    {
        return $this->setValue($id, '');
    }

    /**
     * 批量设置值
     */
    public function setValues(array $values): self
    {
        foreach ($values as $id => $value) {
            $this->setValue($id, $value);
        }
        return $this;
    }

    /**
     * 批量获取值
     */
    public function getValues(array $ids): array
    {
        $values = [];
        foreach ($ids as $id) {
            $values[$id] = $this->getValue($id);
        }
        return $values;
    }

    /**
     * 验证表单
     */
    public function validate(array $rules): array
    {
        $errors = [];

        foreach ($rules as $id => $rule) {
            $value = $this->getValue($id);
            $error = $this->validateField($value, $rule);
            if ($error) {
                $errors[$id] = $error;
            }
        }

        return $errors;
    }

    /**
     * 验证单个字段
     */
    private function validateField($value, array $rules): ?string
    {
        foreach ($rules as $rule => $params) {
            switch ($rule) {
                case 'required':
                    if (empty($value)) {
                        return $params['message'] ?? '此字段是必填的';
                    }
                    break;

                case 'min_length':
                    if (strlen($value) < $params['length']) {
                        return $params['message'] ?? "最少需要{$params['length']}个字符";
                    }
                    break;

                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return $params['message'] ?? '请输入有效的邮箱地址';
                    }
                    break;
            }
        }

        return null;
    }

    /**
     * 重置表单
     */
    public function reset(array $defaultValues = []): self
    {
        foreach ($this->components as $id => $component) {
            $defaultValue = $defaultValues[$id] ?? $this->getDefaultValue($component);
            $this->setValue($id, $defaultValue);
        }
        return $this;
    }

    /**
     * 获取组件的默认值
     */
    private function getDefaultValue(ComponentBuilder $component)
    {
        $config = $component->getConfig(null);

        // 根据组件类型返回默认值
        if (isset($config['type']) && $config['type'] === 'checkbox') {
            return false;
        } elseif (isset($config['items'])) {
            return -1; // combobox
        } else {
            return ''; // entry, label等
        }
    }

    /**
     * 获取状态管理器（用于访问全局状态）
     */
    public function state(): StateManager
    {
        return $this->state;
    }

    /**
     * 魔术方法：支持链式调用
     */
    public function __call(string $method, array $args)
    {
        // 支持类似 $refs->username->setValue('test') 的语法
        if (isset($this->components[$method])) {
            return new ComponentProxy($this->components[$method]);
        }

        throw new BadMethodCallException("Method {$method} not found");
    }
}