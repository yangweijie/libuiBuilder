<?php

namespace Kingbes\Libui\View\Templates;

use InvalidArgumentException;
use Kingbes\Libui\View\Builder;
use Kingbes\Libui\View\Validation\ComponentBuilder;

class FormTemplate
{
    public static function create(array $fields): ComponentBuilder
    {
        $formItems = [];

        foreach ($fields as $field) {
            $row = Builder::hbox()->contains([
                Builder::label()->text($field['label'] . '：'),
                self::createField($field),
            ]);
            $formItems[] = $row;
        }

        return Builder::vbox()->contains($formItems);
    }

    private static function createField(array $field): ComponentBuilder
    {
        switch ($field['type']) {
            case 'text':
                return Builder::entry()->placeholder($field['placeholder'] ?? '');
            case 'checkbox':
                return Builder::checkbox()->text($field['text'] ?? '');
            case 'combobox':
                return Builder::combobox()->items($field['items'] ?? []);
            default:
                throw new InvalidArgumentException("Unknown field type: {$field['type']}");
        }
    }
}

// 使用模板
//$userForm = FormTemplate::create([
//    ['label' => '用户名', 'type' => 'text', 'placeholder' => '请输入用户名'],
//    ['label' => '密码', 'type' => 'password', 'placeholder' => '请输入密码'],
//    ['label' => '记住我', 'type' => 'checkbox', 'text' => '下次自动登录'],
//]);