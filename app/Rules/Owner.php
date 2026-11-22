<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Owner implements Rule
{
    private $table_name;
    private $owner_column_name;
    private $value_column_name;

    /**
     * Create a new rule instance.
     *
     * @param $table_name
     * @param $owner_column_name
     * @param $value_column_name
     */
    public function __construct($table_name, $owner_column_name, $value_column_name = 'id')
    {
        $this->table_name = $table_name;
        $this->owner_column_name = $owner_column_name;
        $this->value_column_name = $value_column_name;
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
        $item = \DB::table($this->table_name)
            ->where($this->value_column_name, $value)
            ->where($this->owner_column_name, auth()->guard('user_customer')->id())
            ->first();
        if ($item)
        {
            return true;
        }
        else
        {
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
        return 'عدم احراز مالکیت! شما دسترسی به رکورد درخواست شده را ندارید.';
    }
}
