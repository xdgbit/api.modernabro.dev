<?php

namespace App\Rules\UserGuest;

use App\Models\User\UserGuest;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueGuestMobileNumberForUpdate implements ValidationRule
{
    public function __construct(
        private ?int $ignoreId = null,
        private ?string $message = null
    )
    {
    }

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void
    {
        $query = UserGuest::query()
            ->where('mobile_number', $value);

        if ($this->ignoreId)
        {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists())
        {
            $fail(
                $this->message ??
                'این شماره موبایل قبلاً برای مهمان دیگری ثبت شده است.'
            );
        }
    }
}