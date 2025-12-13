<?php

namespace Kingbes\Libui\View\Components;

use Exception;
use Kingbes\Libui\Group;
use Kingbes\Libui\View\ComponentBuilder;
use FFI\CData;

/**
 * GroupBuilder - 分组控件构建器
 * 
 * 用于创建带有标题的控件分组，可以将相关控件组织在一起
 */
class GroupBuilder extends ComponentBuilder
{
    protected function getDefaultConfig(): array
    {
        return [
            'title' => '',
            'margined' => false,
        ];
    }

    protected function createNativeControl(): CData
    {
        return Group::create('');
    }

    protected function applyConfig(): void
    {
        $title = $this->getConfig('title');
        Group::setTitle($this->handle, $title);

        $margined = $this->getConfig('margined');
        Group::setMargined($this->handle, $margined ? 1 : 0);
    }

    protected function canHaveChildren(): bool
    {
        return true;
    }

    protected function buildChildren(): void
    {
        // 获取分组内容组件
        $content = $this->children[0] ?? null;
        
        if ($content) {
            // 构建内容组件并将其设置为分组的子控件
            $contentHandle = $content->build();
            Group::setChild($this->handle, $contentHandle);
        }
    }

    /**
     * 设置分组标题
     */
    public function title(string $title): static
    {
        return $this->setConfig('title', $title);
    }

    /**
     * 设置是否带边距
     */
    public function margined(bool $margined = true): static
    {
        return $this->setConfig('margined', $margined);
    }

    /**
     * 设置分组内容
     */
    public function contains($children): static
    {
        if(is_array($children)) {
            if(count($children) > 1){
                throw new Exception('Group 容器只能有一个子组件');
            }
        }else{
            $children = [$children];
        }

        $this->children = $children;
        return $this;
    }
}