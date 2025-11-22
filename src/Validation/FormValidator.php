<?php
namespace Kingbes\Libui\View\Validation;
class FormValidator
{
    private array $rules = [];
    private array $errors = [];

    public function rule(string $field, array $rules): self
    {
        $this->rules[$field] = $rules;
        return $this;
    }

    public function validate(array $data): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $value = $data[$field] ?? null;

            foreach ($rules as $rule => $params) {
                if (!$this->checkRule($field, $value, $rule, $params)) {
                    break; // 一个字段出错就停止验证该字段
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function checkRule(string $field, $value, string $rule, $params): bool
    {
        switch ($rule) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field] = "{$field}不能为空";
                    return false;
                }
                break;

            case 'min_length':
                if (strlen($value) < $params) {
                    $this->errors[$field] = "{$field}长度不能少于{$params}位";
                    return false;
                }
                break;

            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = "{$field}格式不正确";
                    return false;
                }
                break;
        }

        return true;
    }
}

// 使用验证器
//$validator = new FormValidator();
//$validator->rule('username', ['required' => true, 'min_length' => 3])
//    ->rule('password', ['required' => true, 'min_length' => 6])
//    ->rule('email', ['required' => true, 'email' => true]);
//
//// 在表单提交时验证
//if ($validator->validate($state->dump())) {
//    echo "验证通过\n";
//} else {
//    foreach ($validator->getErrors() as $field => $error) {
//        echo "错误: {$error}\n";
//    }
//}