<?php

namespace Kingbes\Libui\View\Components;

use Kingbes\Libui\View\ComponentBuilder;
use Kingbes\Libui\Draw;
use Kingbes\Libui\TextWeight;
use Kingbes\Libui\TextItalic;
use Kingbes\Libui\TextStretch;
use Kingbes\Libui\TextAlign;
use FFI\CData;

/**
 * 文本绘制构建器
 * 用于创建文本布局和绘制文本
 */
class DrawTextBuilder extends ComponentBuilder
{
    private ?CData $fontDesc = null;
    private ?CData $layoutParams = null;
    private ?CData $textLayout = null;

    public function getDefaultConfig(): array
    {
        return [
            'text' => '',
            'family' => 'Arial',
            'size' => 12.0,
            'weight' => TextWeight::Normal,
            'italic' => TextItalic::Normal,
            'stretch' => TextStretch::Normal,
            'width' => -1.0, // -1 表示自动宽度
            'align' => TextAlign::Left,
        ];
    }

    protected function createNativeControl(): CData
    {
        $config = $this->config;
        $this->fontDesc = Draw::createFontDesc(
            $config['family'],
            $config['size'],
            $config['weight'],
            $config['italic'],
            $config['stretch']
        );
        return $this->fontDesc;
    }

    protected function applyConfig(): void
    {
        // 配置已在 createNativeControl 中处理
    }

    /**
     * 设置文本内容
     */
    public function text(string $text): static
    {
        $this->setConfig('text', $text);
        $this->layoutParams = null;
        $this->textLayout = null;
        return $this;
    }

    /**
     * 设置字体家族
     */
    public function family(string $family): static
    {
        $this->setConfig('family', $family);
        $this->fontDesc = null;
        $this->layoutParams = null;
        $this->textLayout = null;
        return $this;
    }

    /**
     * 设置字体大小
     */
    public function size(float $size): static
    {
        $this->setConfig('size', max(1.0, $size));
        $this->fontDesc = null;
        $this->layoutParams = null;
        $this->textLayout = null;
        return $this;
    }

    /**
     * 设置字体重量
     */
    public function weight(TextWeight $weight): static
    {
        $this->setConfig('weight', $weight);
        $this->fontDesc = null;
        $this->layoutParams = null;
        $this->textLayout = null;
        return $this;
    }

    /**
     * 设置斜体
     */
    public function italic(TextItalic $italic): static
    {
        $this->setConfig('italic', $italic);
        $this->fontDesc = null;
        $this->layoutParams = null;
        $this->textLayout = null;
        return $this;
    }

    /**
     * 设置字体拉伸
     */
    public function stretch(TextStretch $stretch): static
    {
        $this->setConfig('stretch', $stretch);
        $this->fontDesc = null;
        $this->layoutParams = null;
        $this->textLayout = null;
        return $this;
    }

    /**
     * 设置文本宽度限制
     */
    public function width(float $width): static
    {
        $this->setConfig('width', $width);
        $this->layoutParams = null;
        $this->textLayout = null;
        return $this;
    }

    /**
     * 设置自动宽度
     */
    public function autoWidth(): static
    {
        return $this->width(-1.0);
    }

    /**
     * 设置文本对齐方式
     */
    public function align(TextAlign $align): static
    {
        $this->setConfig('align', $align);
        $this->layoutParams = null;
        $this->textLayout = null;
        return $this;
    }

    /**
     * 左对齐
     */
    public function alignLeft(): static
    {
        return $this->align(TextAlign::Left);
    }

    /**
     * 居中对齐
     */
    public function alignCenter(): static
    {
        return $this->align(TextAlign::Center);
    }

    /**
     * 右对齐
     */
    public function alignRight(): static
    {
        return $this->align(TextAlign::Right);
    }

    /**
     * 设置字体重量为粗体
     */
    public function bold(): static
    {
        return $this->weight(TextWeight::Bold);
    }

    /**
     * 设置斜体
     */
    public function italicOn(): static
    {
        return $this->italic(TextItalic::Italic);
    }

    /**
     * 常用字体快捷方法
     */
    public function fontArial(): static
    {
        return $this->family('Arial');
    }

    public function fontTimesNewRoman(): static
    {
        return $this->family('Times New Roman');
    }

    public function fontCourierNew(): static
    {
        return $this->family('Courier New');
    }

    public function fontMicrosoftYaHei(): static
    {
        return $this->family('Microsoft YaHei');
    }

    public function fontSimSun(): static
    {
        return $this->family('SimSun');
    }

    /**
     * 常用字号快捷方法
     */
    public function sizeSmall(): static
    {
        return $this->size(10.0);
    }

    public function sizeNormal(): static
    {
        return $this->size(12.0);
    }

    public function sizeMedium(): static
    {
        return $this->size(14.0);
    }

    public function sizeLarge(): static
    {
        return $this->size(18.0);
    }

    public function sizeHuge(): static
    {
        return $this->size(24.0);
    }

    /**
     * 获取文本布局句柄
     */
    public function getTextLayout(): CData
    {
        $this->ensureTextLayout();
        return $this->textLayout;
    }

    /**
     * 获取文本尺寸
     */
    public function getExtents(): array
    {
        $this->ensureTextLayout();
        $width = 0.0;
        $height = 0.0;
        Draw::textLayoutExtents($this->textLayout, $width, $height);
        return ['width' => $width, 'height' => $height];
    }

    /**
     * 确保字体描述已创建
     */
    private function ensureFontDesc(): void
    {
        if ($this->fontDesc === null) {
            $this->handle = $this->createNativeControl();
        }
    }

    /**
     * 确保文本布局已创建
     */
    private function ensureTextLayout(): void
    {
        $this->ensureFontDesc();

        if ($this->textLayout === null) {
            $config = $this->config;
            $ffi = Draw::ffi();

            // 创建字符串
            $str = $ffi->new("char[" . strlen($config['text']) + 1 . "]");
            $ffi->memcpy($str, $config['text'], strlen($config['text']));

            $this->layoutParams = Draw::createTextLayoutParams(
                $str,
                $this->fontDesc,
                $config['width'],
                $config['align']
            );

            $this->textLayout = Draw::createTextLayout($this->layoutParams);
        }
    }

    /**
     * 释放文本布局资源
     */
    public function free(): void
    {
        if ($this->textLayout !== null) {
            Draw::freeTextLayout($this->textLayout);
            $this->textLayout = null;
        }
    }

    /**
     * 创建默认文本布局快捷方法
     */
    public static function create(string $text, string $family = 'Arial', float $size = 12.0): static
    {
        return (new static())
            ->text($text)
            ->family($family)
            ->size($size);
    }

    /**
     * 创建标题文本快捷方法
     */
    public static function title(string $text, string $family = 'Arial'): static
    {
        return (new static())
            ->text($text)
            ->family($family)
            ->size(24.0)
            ->bold();
    }

    /**
     * 创建副标题文本快捷方法
     */
    public static function subtitle(string $text, string $family = 'Arial'): static
    {
        return (new static())
            ->text($text)
            ->family($family)
            ->size(18.0)
            ->bold();
    }

    /**
     * 创建正文文本快捷方法
     */
    public static function body(string $text, string $family = 'Arial'): static
    {
        return (new static())
            ->text($text)
            ->family($family)
            ->size(14.0);
    }

    public function build(): CData
    {
        $this->ensureTextLayout();
        return $this->textLayout;
    }

    public function __destruct()
    {
        $this->free();
    }
}
