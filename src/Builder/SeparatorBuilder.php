<?php

declare(strict_types=1);

namespace Kingbes\Libui\View\Builder;

use FFI\CData;
use Kingbes\Libui\Separator;

/**
 * 分隔线构建器
 */
class SeparatorBuilder extends ComponentBuilder
{
    /**
     * 构建分隔线
     *
     * @return CData 分隔线句柄
     */
    public function build(): CData
    {
        // 创建水平分隔线
        $this->handle = Separator::createHorizontal();

        // 注册到状态管理器
        if ($this->id && $this->stateManager) {
            $this->stateManager->registerComponent($this->id, $this);
        }

        return $this->handle;
    }

    /**
     * 获取组件类型
     *
     * @return string
     */
    public function getType(): string
    {
        return 'separator';
    }
}
