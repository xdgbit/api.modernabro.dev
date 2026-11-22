<?php

namespace App\Rules\UserCustomer;

use Closure;
use App\Models\User\Customer\UserCustomer;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueUserCustomerMobileNumber implements ValidationRule
{
    public function __construct(
        private ?int    $ignoreId = null,
        private ?string $message = null
    )
    {
    }

    public function validate(
        string  $attribute,
        mixed   $value,
        Closure $fail
    ): void
    {
        $query = UserCustomer::query()
            ->where('mobile_number', $value);
        if ($this->ignoreId)
        {
            $query->where('id', '!=', $this->ignoreId);
        }
        if ($query->exists())
        {
            $fail(
                $this->message ??
                'این شماره موبایل قبلاً برای مشتری دیگری ثبت شده است.'
            );
        }
    }
}