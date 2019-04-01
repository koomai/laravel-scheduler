<?php

namespace Koomai\Scheduler\Rules;

use Illuminate\Contracts\Validation\Rule;

class JobRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return class_exists($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('scheduler::messages.invalid_job_class');
    }
}
