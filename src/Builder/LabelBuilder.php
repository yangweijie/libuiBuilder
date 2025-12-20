<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Label;
use Kingbes\Libui\Align;

/**
 * 标签构建器
 */
class LabelBuilder extends ComponentBuilder
{
    /**
     * 设置标签文本
     *
     * @param string $text 文本
     * @return $this
     */
    public function text(string $text): self
    {
        $this->config['text'] = $text;
        return $this;
    }

    

    /**
     * 设置对齐方式
     *
     * @param string $horizontal 水平对齐 (fill, start, center, end)
     * @param string|null $vertical 垂直对齐 (fill, start, center, end)
     * @return $this
     */
    public function align(string $horizontal, ?string $vertical = null): self
    {
        $this->config['align_horizontal'] = $horizontal;
        
        if ($vertical !== null) {
            $this->config['align_vertical'] = $vertical;
        }
        
        return $this;
    }

    /**
     * 构建标签
     *
     * @return CData 标签句柄
     */
    protected function buildComponent(): CData
    {
        $text = $this->config['text'] ?? 'Label';
        
        // 创建标签
        return Label::create($text);
    }

    /**
     * 获取组件类型
     *
     * @return string
     */
    public function getType(): string
    {
        return 'label';
    }

    /**
     * 获取标签文本
     *
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->config['text'] ?? null;
    }

    /**
     * 设置标签文本（动态更新）
     *
     * @param string $text
     * @return $this
     */
    public function setText(string $text): self
    {
        $this->config['text'] = $text;
        
        if ($this->handle) {
            Label::setText($this->handle, $text);
        }
        
        return $this;
    }

    /**
     * 获取组件值（覆盖默认实现）
     * 
     * 对于标签，值优先使用 text 属性
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->config['value'] ?? $this->config['text'] ?? null;
    }

    /**
     * 设置组件值（覆盖默认实现）
     * 
     * 对于标签，如果是字符串则更新文本
     *
     * @param mixed $value
     * @return self
     */
    public function setValue(mixed $value): self
    {
        $this->config['value'] = $value;
        if (is_string($value)) {
            $this->setText($value);
        }
        return $this;
    }

    /**
     * 更新组件显示值（覆盖默认实现）
     *
     * @param mixed $value
     * @return void
     */
    protected function updateComponentValue(mixed $value): void
    {
        if (is_string($value)) {
            $this->setText($value);
        }
    }
}
