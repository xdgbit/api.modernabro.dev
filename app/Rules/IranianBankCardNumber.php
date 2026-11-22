<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IranianBankCardNumber implements Rule
{
    /**
     * @var string|false
     */
    private $message = false;

    /**
     * @var string
     */
    private $type = 'normal';

    /**
     * @var string|null
     */
    private $default_message = "شماره کارت بانکی وارد شده نا معتبر است.";


    /**
     * Create a new rule instance.
     *
     * @param string $type
     * @param string $message
     */
    public function __construct(string $type = '', string $message = '')
    {
        if ($message != '')
        {
            $this->message = $message;
        }

        if ($type != '')
        {
            $this->type = $type;
        }
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param mixed $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $type = $this->type;
        if ($type === 'separate')
        {
            if (!preg_match('/^\d{4}-\d{4}-\d{4}-\d{4}$/', $value))
            {
                return false;
            }
            $value = str_replace('-', '', $value);
        }
        else
        {
            if ($type == 'space')
            {
                if (!preg_match('/^\d{4}\s\d{4}\s\d{4}\s\d{4}$/', $value))
                {
                    return false;
                }
                $value = str_replace(' ', '', $value);
            }
            else
            {
                if (!preg_match('/^\d{16}$/', $value))
                {
                    return false;
                }
            }
        }

        $sum = 0;

        for ($position = 1; $position <= 16; $position++)
        {
            $temp = $value[ $position - 1 ];
            $temp = $position % 2 === 0 ? $temp : $temp * 2;
            $temp = $temp > 9 ? $temp - 9 : $temp;

            $sum += $temp;
        }

        return ($sum % 10 === 0);
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
