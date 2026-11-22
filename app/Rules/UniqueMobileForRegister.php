<?php

namespace App\Rules;

use App\Models\User\Customer\UserCustomerMobile;
use Illuminate\Contracts\Validation\Rule;

class UniqueMobileForRegister implements Rule
{
    private $message;

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
        $value = ConvertNumbersFatoEn($value); // تبدیل اعداد فارسی به انگلیسی

        // بررسی اینکه شماره موبایل قبلاً توسط شخص دیگری ثبت و تایید شده باشد
        $exists = UserCustomerMobile::where('mobile_number', $value)
            ->approved()
            ->exists();

        if ($exists)
        {
            $this->message = 'این شماره همراه قبلاً ثبت شده است.';

            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
