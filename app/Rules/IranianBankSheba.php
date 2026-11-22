<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IranianBankSheba implements Rule
{
    /**
     * @var string|null
     */
    private $message;

    /**
     * @var string|null
     */
    private $default_message = "شماره شبا وارد شده نا معتبر است.";


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
        $ibanReplaceValues = [];
        $exist_IR_prefix = substr($value, 0, 2) === 'IR';

        if (!$exist_IR_prefix)
        {
            $value = 'IR' . $value;
        }

        if (!empty($value))
        {
            $value = preg_replace('/[\W_]+/', '', strtoupper($value));

            if ((4 > strlen($value) || strlen($value) > 34) || (is_numeric($value [0]) || is_numeric($value [1])) || (!is_numeric($value [2]) || !is_numeric($value [3])))
            {
                return false;
            }

            $ibanReplaceChars = range('A', 'Z');

            foreach (range(10, 35) as $tempvalue)
            {
                $ibanReplaceValues[] = strval($tempvalue);
            }

            $tmpIBAN = substr($value, 4) . substr($value, 0, 4);
            $tmpIBAN = str_replace($ibanReplaceChars, $ibanReplaceValues, $tmpIBAN);
            $tmpValue = intval(substr($tmpIBAN, 0, 1));

            for ($i = 1; $i < strlen($tmpIBAN); $i++)
            {
                $tmpValue *= 10;
                $tmpValue += intval(substr($tmpIBAN, $i, 1));
                $tmpValue %= 97;
            }

            if ($tmpValue != 1)
            {
                return false;
            }

            return true;
        }

        return false;
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
