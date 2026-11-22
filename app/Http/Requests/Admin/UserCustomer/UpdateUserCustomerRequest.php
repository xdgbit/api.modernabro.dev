<?php

namespace App\Http\Requests\Admin\UserCustomer;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UserCustomer\UniqueUserCustomerMobileNumber;

class UpdateUserCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userCustomerId = $this->route('customer')?->id;

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

            'mobile_number' => [
                'required',
                'string',
                'max:20',
                new UniqueUserCustomerMobileNumber($userCustomerId),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'وارد کردن نام الزامی است.',
            'first_name.string'   => 'نام باید به صورت متن باشد.',
            'first_name.max'      => 'نام نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'last_name.required' => 'وارد کردن نام خانوادگی الزامی است.',
            'last_name.string'   => 'نام خانوادگی باید به صورت متن باشد.',
            'last_name.max'      => 'نام خانوادگی نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'mobile_number.required' => 'وارد کردن شماره موبایل الزامی است.',
            'mobile_number.string'   => 'شماره موبایل باید به صورت متن باشد.',
            'mobile_number.max'      => 'شماره موبایل نمی‌تواند بیشتر از ۲۰ کاراکتر باشد.',

            'password.string' => 'رمز عبور باید به صورت متن باشد.',
            'password.min'    => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',

            'is_active.boolean' => 'وضعیت فعال بودن باید صحیح یا غلط باشد.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name'    => 'نام',
            'last_name'     => 'نام خانوادگی',
            'mobile_number' => 'شماره موبایل',
            'password'      => 'رمز عبور',
            'is_active'     => 'وضعیت فعال بودن',
        ];
    }
}