<?php

namespace Kingbes\Libui\Declarative\Components;

// 增强的多行文本框组件
use FFI\CData;
use Kingbes\Libui\Declarative\EventBus;
use Kingbes\Libui\Declarative\StateManager;
use Kingbes\Libui\MultilineEntry;

class TextAreaComponent extends Component
{
    public function getTagName(): string
    {
        return 'ui:textarea';
    }
    
    // 定义组件支持的属性
    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'wrapping', 'placeholder', 'readonly', 'max-length'
        ]);
    }

    public function render(): CData
    {
        $wrapping = (bool)$this->getAttribute('wrapping', false);

        $textarea = $wrapping ?
            MultilineEntry::create() :
            MultilineEntry::createNonWrapping();

        // v-model双向绑定
        if ($model = $this->getAttribute('v-model')) {
            $initialValue = StateManager::get($model, '');
            MultilineEntry::setText($textarea, $initialValue);

            StateManager::watch($model, function($newValue) use ($textarea) {
                MultilineEntry::setText($textarea, $newValue);
            });
        }

        // 绑定变化事件
        MultilineEntry::onChanged($textarea, function() use ($textarea, $model) {
            $currentText = MultilineEntry::text($textarea);

            if ($model) {
                StateManager::set($model, $currentText);
            }

            if (isset($this->eventHandlers['change'])) {
                $this->eventHandlers['change']($currentText);
            }

            // 字数统计事件
            $wordCount = str_word_count($currentText);
            $charCount = strlen($currentText);

            if (isset($this->eventHandlers['word_count'])) {
                $this->eventHandlers['word_count']($wordCount, $charCount);
            }

            EventBus::emit('textarea:changed', $this->ref, $currentText, $wordCount, $charCount);

            // 长度验证
            if ($maxLength = $this->getAttribute('max-length')) {
                $isValid = $charCount <= (int)$maxLength;
                EventBus::emit('textarea:length_validated', $this->ref, $isValid, $charCount, $maxLength);
            }
        });

        // 占位符支持（模拟）
        if ($placeholder = $this->getAttribute('placeholder')) {
            $this->addPlaceholderSupport($textarea, $placeholder);
        }

        // 只读模式
        if ($readonly = $this->getAttribute('readonly')) {
            MultilineEntry::setReadOnly($textarea, (bool)$readonly);
        }

        $this->handle = $textarea;
        return $textarea;
    }

    private function addPlaceholderSupport(CData $textarea, string $placeholder): void
    {
        // 模拟占位符功能
        $currentText = MultilineEntry::text($textarea);
        if (empty($currentText)) {
            // 设置占位符样式（需要额外实现）
            EventBus::emit('textarea:placeholder_shown', $this->ref, $placeholder);
        }
    }

    public function getValue()
    {
        return $this->handle ? MultilineEntry::text($this->handle) : '';
    }

    public function setValue($value): void
    {
        if ($this->handle) {
            MultilineEntry::setText($this->handle, (string)$value);
        }
    }
}