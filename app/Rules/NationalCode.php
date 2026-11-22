<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NationalCode implements ValidationRule
{
    public function __construct(
        private ?string $message = null,
        private bool    $bypassed = false
    )
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->bypassed)
        {
            return;
        }

        if (
            !preg_match('/^\d{8,10}$/', $value) ||
            preg_match('/^(\d)\1{9}$/', str_pad($value, 10, '0', STR_PAD_LEFT))
        )
        {
            $fail($this->message ?? 'کد ملی نامعتبر است.');

            return;
        }

        $value = str_pad($value, 10, '0', STR_PAD_LEFT);

        $sum = 0;

        for ($i = 0; $i <= 8; $i++)
        {
            $sum += $value[ $i ] * (10 - $i);
        }

        $remainder = $sum % 11;

        $control = $remainder < 2
            ? $remainder
            : 11 - $remainder;

        if ((int)$value[9] !== $control)
        {
            $fail($this->message ?? 'کد ملی نامعتبر است.');
        }
    }
}