<?php

namespace Koomai\Scheduler\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Artisan;

class ArtisanCommandRule implements Rule
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
        return array_key_exists(strtok($value, ' '), Artisan::all());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('scheduler::messages.invalid_artisan_command');
    }
}
