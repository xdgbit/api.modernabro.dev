<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class JustPersian implements Rule
{

    private $message;

    /**
     * Create a new rule instance.
     *
     * @param string $message
     */
    public function __construct(string $message = null)
    {
        $this->message = $message;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $value_without_space = str_replace(' ','',$value);
        return preg_match("/^[\x{0600}-\x{06FF}]*$/u", $value_without_space);

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message ?: 'باید حتما یک عبارت تماما فارسی باشد.';
    }
}
