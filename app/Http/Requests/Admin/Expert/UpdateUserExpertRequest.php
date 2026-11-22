<?php

namespace App\Http\Requests\Admin\Expert;

use App\Rules\NationalCode;
use App\Rules\UserExpert\UniqueUserExpertMobileNumber;
use App\Rules\UserExpert\UniqueUserExpertNationalCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserExpertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userExpertId = $this->route('expert')?->id;

        return [
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'role_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('roles', 'id')
                    ->where('guard_name', 'user_expert'),
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
                new UniqueUserExpertNationalCode($userExpertId),
            ],

            'mobile_number' => [
                'nullable',
                'string',
                'max:20',
                new UniqueUserExpertMobileNumber($userExpertId),
            ],

            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('user_experts', 'email')->ignore($userExpertId),
            ],

            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                Rule::unique('user_experts', 'username')->ignore($userExpertId),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:255',
                'confirmed',
            ],

            'is_active' => [
                'required',
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

            'national_code.string'   => 'کد ملی باید به صورت متن باشد.',
            'national_code.size'     => 'کد ملی باید ۱۰ رقم باشد.',
            'national_code.required' => 'کد ملی نام الزامی است.',

            'mobile_number.string'   => 'شماره موبایل باید به صورت متن باشد.',
            'mobile_number.max'      => 'شماره موبایل نمی‌تواند بیشتر از ۲۰ کاراکتر باشد.',
            'mobile_number.required' => 'شماره موبایل نام الزامی است.',

            'email.string' => 'ایمیل باید به صورت متن باشد.',
            'email.email'  => 'فرمت ایمیل معتبر نیست.',
            'email.max'    => 'ایمیل نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',

            'username.required'   => 'وارد کردن نام کاربری الزامی است.',
            'username.string'     => 'نام کاربری باید به صورت متن باشد.',
            'username.min'        => 'نام کاربری باید حداقل ۳ کاراکتر باشد.',
            'username.max'        => 'نام کاربری نمی‌تواند بیشتر از ۵۰ کاراکتر باشد.',
            'username.alpha_dash' => 'نام کاربری فقط می‌تواند شامل حروف انگلیسی، عدد، خط تیره و زیرخط باشد.',
            'username.unique'     => 'این نام کاربری قبلاً ثبت شده است.',

            'password.string'    => 'رمز عبور باید به صورت متن باشد.',
            'password.min'       => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
            'password.max'       => 'رمز عبور نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور مطابقت ندارد.',

            'is_active.required' => 'وضعیت فعال بودن الزامی است.',
            'is_active.boolean'  => 'وضعیت فعال بودن باید صحیح یا غلط باشد.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name'    => 'نام',
            'last_name'     => 'نام خانوادگی',
            'national_code' => 'کد ملی',
            'mobile_number' => 'شماره موبایل',
            'email'         => 'ایمیل',
            'username'      => 'نام کاربری',
            'password'      => 'رمز عبور',
            'is_active'     => 'وضعیت فعال بودن',
        ];
    }
}