<?php

namespace App\Rules;

use App\Models\User\Customer\UserCustomer;
use App\Models\User\Customer\UserCustomerPhone;
use Illuminate\Contracts\Validation\Rule;

class UniquePhone implements Rule
{
    /**
     * @var string|null
     */
    private $message;
    private $user_customer;

    /**
     * @var string|null
     */
    private $default_message = "شماره تلفن وارد شده توسط شخص دیگری ثبت و تایید گردیده است.";


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
        $user_customer_id = $this->user_customer->id;
        $exist_approved_phone_for_other = UserCustomerPhone::where('phone_number', $value)
            ->where('user_customer_id', '!=', $user_customer_id)
            ->approved()
            ->first();

        if ($exist_approved_phone_for_other)
        {
            $this->default_message = 'شماره تلفن وارد شده برای کاربر دیگری تایید شده است.';

            return false;
        }
        else
        {
            $last_active_phone = UserCustomerPhone::where('user_customer_id', $user_customer_id)
                ->notArchive()
                ->last()
                ->first();
            if ($last_active_phone)
            {
                if ($last_active_phone->status === 'approved')
                {
                    if ($last_active_phone->phone_number === $value)
                    {
                        $this->default_message = 'شماره تلفن وارد شده هم اکنون برای شما ثبت و تایید شده است.';

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
