<?php

namespace App\Rules;

use App\Models\User\Customer\UserCustomer;
use Illuminate\Contracts\Validation\Rule;

class VerifiableEmail implements Rule
{
    /**
     * @var string|null
     */
    private $message;

    /**
     * @var string|null
     */
    private $default_message = "ایمیل مشتری مورد نظر قبلا برای شخص دیگری تایید شده و برای ایشان قابل تایید نمی باشد.";


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
        $user_customer = UserCustomer::find($value);

        if ($user_customer)
        {
            $exist_other_verified_email_user_customer = UserCustomer::where('email', $user_customer->email)
                ->whereNotNull('email_verified_at')
                ->where('id', '<>', $user_customer->id)
                ->first();

            if ($exist_other_verified_email_user_customer)
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        else
        {
            $this->default_message = 'اطلاعات کاربری مشتری یافت نشد.';

            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message ? $this->message : $this->default_message;
    }
}
