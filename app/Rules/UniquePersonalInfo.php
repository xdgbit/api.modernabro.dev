<?php

namespace App\Rules;

use App\Models\User\Customer\UserCustomer;
use App\Models\User\Customer\UserCustomerPersonalInfo;
use Illuminate\Contracts\Validation\Rule;

class UniquePersonalInfo implements Rule
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
    private $user_customer;

    /**
     * @var string {"iranian","non_iranian"}
     */
    private $national_type;


    /**
     * Create a new rule instance.
     *
     * @param UserCustomer $user_customer
     * @param string{"iranian","non_iranian"} $national_type
     * @param string $message
     */
    public function __construct(UserCustomer $user_customer, $national_type = "iranian", string $message = null)
    {
        $this->message = $message;
        $this->user_customer = $user_customer;
        $this->national_type = $national_type;
        $this->set_unique_filed_name($national_type);
    }

    private function set_unique_filed_name($national_type)
    {
        switch ($national_type)
        {
            case 'non_iranian':
            {
                $this->unique_filed_name = 'pervasive_code';
                break;
            }
            case 'iranian':
            default:
            {
                $this->unique_filed_name = 'national_code';
                break;
            }
        }
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
        $exist_approved_personal_info = UserCustomerPersonalInfo::where('national_type', $this->national_type)
            ->where($this->unique_filed_name, $value)
            ->where('user_customer_id', '!=', $user_customer_id)
            ->approved()
            ->first();

        if ($exist_approved_personal_info)
        {
            $this->default_message = 'توسط شخص دیگری ثبت و تایید شده است.';

            return false;
        }
        else
        {
            return true;
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
