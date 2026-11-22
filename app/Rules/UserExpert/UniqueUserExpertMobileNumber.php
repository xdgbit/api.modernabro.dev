<?php

namespace App\Rules\UserExpert;

use Closure;
use App\Models\User\UserExpert;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueUserExpertMobileNumber implements ValidationRule
{
    public function __construct(
        private ?int $ignoreId = null,
        private ?string $message = null
    ) {
    }

    public function validate(
        string $attribute,
        mixed $value,
        Closure $fail
    ): void {
        $query = UserExpert::query()
            ->where('mobile_number', $value);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail(
                $this->message ??
                'این شماره موبایل قبلاً برای کارشناس دیگری ثبت شده است.'
            );
        }
    }
}