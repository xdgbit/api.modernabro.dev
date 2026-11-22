<?php

namespace App\Http\Requests\Admin\Account;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:current_password',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'وارد کردن رمز عبور فعلی الزامی است.',
            'current_password.string'   => 'رمز عبور فعلی باید به صورت متن باشد.',

            'password.required'  => 'وارد کردن رمز عبور جدید الزامی است.',
            'password.string'    => 'رمز عبور جدید باید به صورت متن باشد.',
            'password.min'       => 'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور جدید مطابقت ندارد.',
            'password.different' => 'رمز عبور جدید باید با رمز عبور فعلی متفاوت باشد.',
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'رمز عبور فعلی',
            'password'         => 'رمز عبور جدید',
        ];
    }
}