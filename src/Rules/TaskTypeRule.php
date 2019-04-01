<?php

namespace Koomai\Scheduler\Rules;

use Illuminate\Contracts\Validation\Rule;
use Koomai\Scheduler\Constants\TaskType;

class TaskTypeRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return bool
     * @throws \ReflectionException
     */
    public function passes($attribute, $value)
    {
        return in_array(strtolower($value), array_map('strtolower', TaskType::keys()));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('scheduler::messages.invalid_task_type');
    }
}
