<?php

namespace Kingbes\Libui\Declarative\Components;
// 增强的日期时间选择器组件
use FFI\CData;
use Kingbes\Libui\DateTimePicker;
use Kingbes\Libui\Declarative\EventBus;
use Kingbes\Libui\Declarative\StateManager;

class DateTimePickerComponent extends Component
{
    private string $pickerType;

    public function getTagName(): string
    {
        return 'ui:datetime';
    }
    
    // 定义组件支持的属性
    protected function getSupportedAttributes(): array
    {
        return array_merge(parent::getSupportedAttributes(), [
            'type', 'min-date', 'max-date'
        ]);
    }

    public function render(): CData
    {
        $this->pickerType = $this->getAttribute('type', 'datetime');

        $picker = match($this->pickerType) {
            'date' => DateTimePicker::createDate(),
            'time' => DateTimePicker::createTime(),
            default => DateTimePicker::createDataTime(),
        };

        // v-model双向绑定
        if ($model = $this->getAttribute('v-model')) {
            $initialValue = StateManager::get($model);
            if ($initialValue) {
                // 设置初始时间（需要转换格式）
                $this->setDateTimeValue($picker, $initialValue);
            }

            StateManager::watch($model, function($newValue) use ($picker) {
                $this->setDateTimeValue($picker, $newValue);
            });
        }

        // 绑定变化事件
        DateTimePicker::onChanged($picker, function() use ($picker, $model) {
            $currentDateTime = $this->getDateTimeValue($picker);

            if ($model) {
                StateManager::set($model, $currentDateTime);
            }

            if (isset($this->eventHandlers['change'])) {
                $this->eventHandlers['change']($currentDateTime);
            }

            // 日期验证事件
            if (isset($this->eventHandlers['validate'])) {
                $isValid = $this->eventHandlers['validate']($currentDateTime);
                EventBus::emit('datetime:validated', $this->ref, $currentDateTime, $isValid);
            }

            EventBus::emit('datetime:changed', $this->ref, $currentDateTime, $this->pickerType);
        });

        // 范围限制
        if ($minDate = $this->getAttribute('min-date')) {
            // 添加最小日期限制逻辑
            $this->addDateRangeValidation($picker, 'min', $minDate);
        }

        if ($maxDate = $this->getAttribute('max-date')) {
            // 添加最大日期限制逻辑
            $this->addDateRangeValidation($picker, 'max', $maxDate);
        }

        $this->handle = $picker;
        return $picker;
    }

    private function setDateTimeValue(CData $picker, $value): void
    {
        // 实现日期时间设置逻辑
        if (is_string($value)) {
            $timestamp = strtotime($value);
            $datetime = new \DateTime();
            $datetime->setTimestamp($timestamp);
            // 使用libui API设置日期时间
            DateTimePicker::setTime($picker, $datetime);
        }
    }

    private function getDateTimeValue(CData $picker)
    {
        // 获取当前日期时间值
        $time = DateTimePicker::time($picker);
        return $time; // 返回DateTime对象或格式化字符串
    }

    private function addDateRangeValidation(CData $picker, string $type, string $date): void
    {
        EventBus::on("datetime:validate_{$this->ref}", function($currentDate) use ($type, $date) {
            $limitTimestamp = strtotime($date);
            $currentTimestamp = strtotime($currentDate);

            $isValid = $type === 'min' ?
                $currentTimestamp >= $limitTimestamp :
                $currentTimestamp <= $limitTimestamp;

            EventBus::emit('datetime:range_validated', $this->ref, $isValid, $type);
        });
    }

    public function getValue()
    {
        return $this->handle ? $this->getDateTimeValue($this->handle) : null;
    }

    public function setValue($value): void
    {
        if ($this->handle) {
            $this->setDateTimeValue($this->handle, $value);
        }
    }
}