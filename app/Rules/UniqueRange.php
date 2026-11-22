<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueRange implements Rule
{
    private $message;
    private $table;
    private $table_min_field_name;
    private $table_max_field_name;
    private $max_field_name;
    private $min_field_name;
    private $conditions;
    private $exception_conditions;

    /**
     * Create a new rule instance.
     *
     * @param string $message
     * @param boolean $bypassed
     */

    private function get_exception_ids()
    {
        $exception = $this->exception_conditions;
        if (count($exception) > 0)
        {
            $exception_query = \DB::table($this->table);
            foreach ($this->exception_conditions as $field_name => $value)
            {
                if (is_null($value))
                {
                    $exception_query->whereNull($field_name);
                }
                else
                {
                    $exception_query->where($field_name, $value);
                }
            }

            return $exception_query->get()->pluck('id')->toArray();
        }
        else
        {
            return [];
        }

    }


    public function __construct($table, $table_min_field_name, $table_max_field_name, $min_field_name = null, $max_field_name = null, $conditions = [], $exception_conditions = [], string $message = null)
    {
        $this->message = $message;
        $this->table = $table;
        $this->conditions = $conditions;
        $this->exception_conditions = $exception_conditions;
        $this->table_min_field_name = $table_min_field_name;
        $this->table_max_field_name = $table_max_field_name;
        $this->min_field_name = $min_field_name ? $min_field_name : $table_min_field_name;
        $this->max_field_name = $max_field_name ? $max_field_name : $table_max_field_name;
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
        $amount_min_field_name = request()->get($this->min_field_name);
        $amount_max_field_name = request()->get($this->max_field_name);
        $exception_ids = $this->get_exception_ids();
        $query = \DB::table($this->table);
        if (count($exception_ids) > 0)
        {
            $query->whereNotIn('id', $exception_ids);
        }
        $query->where(function ($q) use ($amount_min_field_name, $amount_max_field_name) {
            $q->where(function ($q) use ($amount_min_field_name, $amount_max_field_name) {
                $q->where($this->table_min_field_name, '<=', $amount_min_field_name)->where($this->table_max_field_name, '>=', $amount_min_field_name);
            })
                ->orWhere(function ($q) use ($amount_min_field_name, $amount_max_field_name) {
                    $q->where($this->table_min_field_name, '<=', $amount_max_field_name)->where($this->table_max_field_name, '>=', $amount_max_field_name);
                })
                ->orWhere(function ($q) use ($amount_min_field_name, $amount_max_field_name) {
                    $q->where($this->table_min_field_name, '>=', $amount_min_field_name)->where($this->table_max_field_name, '<=', $amount_max_field_name);
                });
        });
        foreach ($this->conditions as $field_name => $value)
        {
            if (is_null($value))
            {
                $query->whereNull($field_name);
            }
            else
            {
                $query->where($field_name, $value);
            }
        }

        $res = $query->first();

        return $res ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message ?: 'بازه انتخابی با سایر بازه های ثبت شده تداخل دارد';
    }
}
