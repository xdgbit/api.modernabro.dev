<?php

namespace App\Http\Requests\Admin\Guest;

use App\Rules\NationalCode;
use App\Rules\UserGuest\UniqueGuestMobileNumberForUpdate;
use App\Rules\UserGuest\UniqueGuestNationalCodeForUpdate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGuestRequest extends FormRequest
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
                'nullable',
                'string',
                'size:10',
                new NationalCode('کد ملی نامعتبر است.'),
                new UniqueGuestNationalCodeForUpdate(
                    $this->route('guest')?->id
                ),
            ],

            'mobile_number' => [
                'nullable',
                'string',
                'max:20',
                new UniqueGuestMobileNumberForUpdate(
                    $this->route('guest')?->id
                ),
            ],

            'birth_date' => [
                'nullable',
                'date',
            ],

            'gender' => [
                'nullable',
                'in:male,female',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

        ];
    }
}