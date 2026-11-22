<?php

namespace App\Rules\UserGuest;

use App\Models\User\UserGuest;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use function request;

class UniqueGuestMobileNumber implements ValidationRule
{
    public function __construct(
        private ?string $message = null
    )
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        preg_match('/guests\.(\d+)\.mobile_number/', $attribute, $matches);

        $index = $matches[1] ?? null;

        $guestId = $index !== null
            ? request()->input("guests.$index.id")
            : null;

        $query = UserGuest::query()
            ->where('mobile_number', $value);

        if ($guestId)
        {
            $query->where('id', '!=', $guestId);
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