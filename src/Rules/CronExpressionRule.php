<?php

namespace Koomai\Scheduler\Rules;

use Cron\CronExpression;
use Illuminate\Contracts\Validation\Rule;

class CronExpressionRule implements Rule
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
        return CronExpression::isValidExpression($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('scheduler::messages.invalid_cron_expression');
    }
}
