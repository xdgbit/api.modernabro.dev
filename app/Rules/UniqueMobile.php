<?php

namespace App\Rules;

use App\Models\User\Customer\UserCustomer;
use App\Models\User\Customer\UserCustomerMobile;
use Illuminate\Contracts\Validation\Rule;

class UniqueMobile implements Rule
{
    /**
     * @var string|null
     */
    private $message;
    private $user_customer;

    /**
     * @var string|null
     */
    private $default_message = "شماره همراه وارد شده توسط شخص دیگری ثبت و تایید گردیده است.";


    /**
     * Create a new rule instance.
     *
     * @param UserCustomer $user_customer
     * @param string $message
     */
    public function __construct(UserCustomer $user_customer = null, string $message = null)
    {
        $this->message = $message;
        $this->user_customer = $user_customer ? $user_customer : auth()->guard('user_customer')->user();
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
        $value = ConvertNumbersFatoEn($value);
        $user_customer_id = $this->user_customer->id;
        $exist_approved_mobile_for_other = UserCustomerMobile::where('mobile_number', $value)
            ->where('user_customer_id', '!=', $user_customer_id)
            ->approved()
            ->first();

        if ($exist_approved_mobile_for_other)
        {
            $this->default_message = 'شماره همراه وارد شده برای کاربر دیگری تایید شده است.';

            return false;
        }
        else
        {
            $last_active_mobile = UserCustomerMobile::where('user_customer_id', $user_customer_id)
                ->notArchive()
                ->last()
                ->first();
            if ($last_active_mobile)
            {
                if ($last_active_mobile->status === 'approved')
                {
                    if ($last_active_mobile->mobile_number === $value)
                    {
                        $this->default_message = 'شماره همراه وارد شده هم اکنون برای شما ثبت و تایید شده است.';

                        return false;
                    }
                    else
                    {
                        return true;
                    }
                }
                else
                {
                    return true;
                }
            }
            else
            {
                return true;
            }
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
