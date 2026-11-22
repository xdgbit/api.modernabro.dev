<?php

namespace App\Rules;

use App\Models\User\Customer\UserCustomer;
use App\Models\User\Customer\UserCustomerPersonalInfo;
use Illuminate\Contracts\Validation\Rule;

class ApprovePhone implements Rule
{
    /**
     * @var string|null
     */
    private $message;

    /**
     * @var string|null
     */
    private $default_message;

    /**
     * @var UserCustomer
     */
    private $user_customer_id;

    /**
     * @var string {"iranian","non_iranian"}
     */
    private $national_type;


    /**
     * Create a new rule instance.
     * @param string{"iranian","non_iranian"} $national_type
     * @param string $message
     */
    public function __construct(string $user_customer_id, string $message = null)
    {

        $this->message = $message;
        $this->user_customer_id = $user_customer_id;
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

        $user_customer = UserCustomer::find($this->user_customer_id);
        if (!$user_customer)
        {
            $this->default_message = 'کاربری با این مشخصات یافت نشد';

            return false;
        }
        else
        {
            $last_active_phone = $user_customer->phones()->notArchive()->last()->first();
            if ($last_active_phone)
            {
                if ($last_active_phone->id === $value)
                {
                    $phone_number = $last_active_phone->phone_number;

                    $exist_phone_info = $user_customer->phones()
                        ->where('phone_number', $phone_number)
                        ->approved()
                        ->first();
                    if ($exist_phone_info)
                    {
                        $x = $exist_phone_info->xdgbit_customer_id;

                        $this->default_message = 'این شماره ثابت برای مشتری دیگری با شناسه ' . $x . ' تایید شده است';

                        return false;
                    }
                    else
                    {
                        return true;
                    }
                }
                else
                {
                    $this->default_message = 'اطلاعات  شماره ثابت مورد تایید شما با آخرین اطلاعات یکسان نیست ';

                    return false;
                }
            }
            else
            {
                $this->default_message = 'اطلاعات ثابت فعالی یافت نشد';

                return false;
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
