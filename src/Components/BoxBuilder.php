<?php

namespace Kingbes\Libui\View\Components;

use FFI\CData;
use Kingbes\Libui\Box;
use Kingbes\Libui\View\Validation\ComponentBuilder;

class BoxBuilder extends ComponentBuilder
{
    private string $direction;

    public function __construct(string $direction, array $config = [])
    {
        $this->direction = $direction;
        parent::__construct($config);
    }

    public function getDefaultConfig(): array
    {
        return [
            'padded' => true,
            'stretchy' => false, // 子元素是否可拉伸
        ];
    }

    protected function createNativeControl(): CData
    {
        return $this->direction === 'vertical'
            ? Box::newVerticalBox()
            : Box::newHorizontalBox();
    }

    protected function applyConfig(): void
    {
        Box::setPadded($this->handle, $this->getConfig('padded'));
    }

    protected function canHaveChildren(): bool
    {
        return true;
    }

    protected function buildChildren(): void
    {
        foreach ($this->children as $child) {
            $childHandle = $child->build();
            $stretchy = $child->getConfig('stretchy', $this->getConfig('stretchy'));
            
            // 自动为Checkbox和Radio设置stretchy
            $componentType = get_class($child);
            $componentType = substr($componentType, strrpos($componentType, '\\') + 1);
            
            if ($componentType === 'CheckboxBuilder' || $componentType === 'RadioBuilder') {
                $stretchy = $child->getConfig('stretchy', true); // 默认为stretchy
                echo "[BoxBuilder] Auto-setting {$componentType} to stretchy=true\n";
            }
            
            Box::append($this->handle, $childHandle, $stretchy);
        }
    }

    public function padded(bool $padded = true): static
    {
        return $this->setConfig('padded', $padded);
    }

    public function stretchy(bool $stretchy = true): static
    {
        return $this->setConfig('stretchy', $stretchy);
    }
    
    /**
     * 实现setValue方法以支持数据绑定
     */
    public function setValue($value): void
    {
        // 对于容器组件，控制显示/隐藏
        if (is_bool($value)) {
            if ($value) {
                $this->show();
            } else {
                $this->hide();
            }
        }
    }
}