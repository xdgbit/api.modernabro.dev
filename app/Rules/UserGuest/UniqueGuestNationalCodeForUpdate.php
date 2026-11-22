<?php

namespace App\Rules\UserGuest;

use App\Models\User\UserGuest;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueGuestNationalCodeForUpdate implements ValidationRule
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
            ->where('national_code', $value);

        if ($this->ignoreId)
        {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists())
        {
            $fail(
                $this->message ??
                'این کد ملی قبلاً برای مهمان دیگری ثبت شده است.'
            );
        }
    }
}