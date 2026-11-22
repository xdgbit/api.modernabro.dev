<?php

namespace App\Rules;

use App\Models\User\Customer\UserCustomer;
use App\Models\User\Customer\UserCustomerPersonalInfo;
use Illuminate\Contracts\Validation\Rule;

class ApprovePersonalInfo implements Rule
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
            $last_active_personal_info = $user_customer->personal_infos()->notArchive()->last()->first();
            if ($last_active_personal_info)
            {
                if ($last_active_personal_info->id === $value)
                {
                    $national_type = $last_active_personal_info->national_type;

                    switch ($national_type)
                    {
                        case 'non_iranian':
                        {
                            $unique_filed_name = 'pervasive_code';
                            $fa_unique_filed_name = 'کد فراگیر اتباع خارجی ';

                            break;
                        }
                        case 'iranian':
                        default:
                        {
                            $unique_filed_name = 'national_code';
                            $fa_unique_filed_name = 'کدملی ';
                            break;
                        }
                    }

                    $unique_value=$last_active_personal_info[$unique_filed_name];
                    //$exist_approved_personal_info = $user_customer->personal_infos()
                    $exist_approved_personal_info = UserCustomerPersonalInfo::
                         where($unique_filed_name, $unique_value)
                         ->where('user_customer_id','<>', $this->user_customer_id)
                        ->approved()
                        ->first();
                    if ($exist_approved_personal_info)
                    {
                        $x = $exist_approved_personal_info->xdgbit_customer_id;

                        $this->default_message = $fa_unique_filed_name . 'برای مشتری دیگری با شناسه ' . $x . ' تایید شده است';

                        return false;
                    }
                    else
                    {
                        return true;
                    }
                }
                else
                {
                    $this->default_message = 'اطلاعاتی فردی مورد تایید شما با آخرین اطلاعات یکسان نیست ';

                    return false;
                }
            }
            else
            {
                $this->default_message = 'مشخصات فردی فعالی یافت نشد';

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
