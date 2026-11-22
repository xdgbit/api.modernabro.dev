<?php

namespace App\Rules\UserExpert;

use App\Models\User\UserExpert;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueUserExpertNationalCode implements ValidationRule
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
        $query = UserExpert::query()
            ->where('national_code', $value);

        if ($this->ignoreId !== null)
        {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists())
        {
            $fail(
                $this->message ??
                'این کد ملی قبلاً برای کارشناس دیگری ثبت شده است.'
            );
        }
    }
}