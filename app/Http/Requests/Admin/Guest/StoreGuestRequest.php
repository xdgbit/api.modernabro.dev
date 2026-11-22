<?php

namespace App\Http\Requests\Admin\Guest;

use App\Rules\NationalCode;
use App\Rules\UserGuest\UniqueGuestMobileNumber;
use App\Rules\UserGuest\UniqueGuestNationalCode;
use Illuminate\Foundation\Http\FormRequest;

class StoreGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'national_code' => [
                'required',
                'string',
                'size:10',
                new NationalCode('کد ملی نامعتبر است.'),
                new UniqueGuestNationalCode(),
            ],

            'mobile_number' => [
                'nullable',
                'string',
                'max:20',
                new UniqueGuestMobileNumber(),
            ],

            'birth_date' => [
                'required',
                'date',
            ],

            'gender' => [
                'required',
                'in:male,female',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

        ];
    }
}